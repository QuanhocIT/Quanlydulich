<?php
$pageTitle = 'Chi tiết Tour';
$currentPage = 'tour';
ob_start();
?>

<style>
    .tour-header-section {
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
        transition: transform 0.2s;
    }

    .info-card:hover {
        transform: translateY(-2px);
    }

    .info-label {
        font-weight: 600;
        color: var(--text-muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .info-value {
        font-size: 14px;
        color: var(--text-light);
        margin-top: 5px;
    }

    .nav-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 10px;
    }

    .nav-tab {
        padding: 12px 20px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        color: var(--text-light);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s;
        cursor: pointer;
    }

    .nav-tab:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--accent-gold);
    }

    .nav-tab.active {
        background: rgba(212, 175, 55, 0.2);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .timeline-item {
        position: relative;
        padding-left: 40px;
        padding-bottom: 30px;
        border-left: 2px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 20px;
    }

    .timeline-item:last-child {
        border-left: none;
    }

    .timeline-badge {
        position: absolute;
        left: -15px;
        top: 0;
        width: 30px;
        height: 30px;
        background: var(--accent-gold);
        color: var(--primary-dark);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .schedule-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-left: 4px solid var(--accent-gold);
        border-radius: 2px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s;
    }

    .schedule-card:hover {
        border-left-color: var(--accent-gold);
        background: rgba(45, 45, 45, 0.7);
    }

    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .image-gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 2px;
        aspect-ratio: 4/3;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .image-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .image-gallery-item:hover img {
        transform: scale(1.1);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .status-HoatDong {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .status-NgungHoatDong {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }

    .badge-info {
        background: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }

    .badge-success {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .badge-secondary {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }

    .price-tag {
        font-size: 28px;
        font-weight: 700;
        color: var(--accent-gold);
        margin-top: 10px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--accent-gold);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .two-column-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .two-column-layout {
            grid-template-columns: 1fr;
        }
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

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    .card-body {
        padding: 25px;
    }

    .input-group {
        display: flex;
        gap: 10px;
    }

    .input-group .input {
        flex: 1;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 8px 10px;
        font-size: 12px;
        border-radius: 2px;
    }

    .input-group .btn {
        padding: 8px 15px;
        font-size: 12px;
    }
</style>

<!-- Tour Header -->
<div class="tour-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                <span class="badge badge-secondary">#<?php echo $tour['tour_id']; ?></span>
                <span class="status-badge status-<?php echo $tour['trang_thai'] === 'HoatDong' ? 'HoatDong' : 'NgungHoatDong'; ?>">
                    <?php echo $tour['trang_thai'] === 'HoatDong' ? 'Hoạt động' : 'Ngừng hoạt động'; ?>
                </span>
                <span class="badge badge-info">
                    <?php 
                    $loaiTour = [
                        'TrongNuoc' => 'Trong nước',
                        'QuocTe' => 'Quốc tế',
                        'TheoYeuCau' => 'Theo yêu cầu'
                    ];
                    echo $loaiTour[$tour['loai_tour']] ?? $tour['loai_tour'];
                    ?>
                </span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 15px; color: var(--text-light);">
                <?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?>
            </h1>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                🕐 <?php echo htmlspecialchars($tour['thoi_gian'] ?? 'N/A'); ?>
                <span style="margin: 0 10px;">•</span>
                📍 <?php echo htmlspecialchars($tour['diem_khoi_hanh'] ?? 'N/A'); ?>
                <span style="margin: 0 5px;">→</span>
                🚩 <?php echo htmlspecialchars($tour['diem_den'] ?? 'N/A'); ?>
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="index.php?act=admin/quanLyTour" class="btn btn-secondary">
                ← Quay lại
            </a>
            <a href="index.php?act=tour/update&id=<?php echo $tour['tour_id']; ?>" class="btn btn-primary">
                ✏️ Sửa tour
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

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        ⚠ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php 
    $tourBookingLink = rtrim(BASE_URL, '/') . '/index.php?act=tour/show&id=' . $tour['tour_id'];
    $tourQrPath = !empty($tour['qr_code_path']) ? BASE_URL . $tour['qr_code_path'] : null;
?>

<div class="two-column-layout">
    <!-- Left Column: Info Cards -->
    <div>
        <!-- Price Card -->
        <div class="info-card">
            <div class="card-body" style="text-align: center;">
                <div class="info-label">Giá cơ bản</div>
                <div class="price-tag"><?php echo number_format((float)($tour['gia_co_ban'] ?? 0)); ?> VNĐ</div>
            </div>
        </div>

        <!-- Quick Info -->
        <div class="info-card">
            <div class="card-body">
                <h6 style="font-weight: 600; margin-bottom: 20px; color: var(--accent-gold); font-size: 14px;">Thông tin nhanh</h6>
                <div style="margin-bottom: 20px;">
                    <div class="info-label">📅 Ngày tạo</div>
                    <div class="info-value">
                        <?php echo isset($tour['ngay_tao']) ? date('d/m/Y', strtotime($tour['ngay_tao'])) : 'N/A'; ?>
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <div class="info-label">🚗 Phương tiện</div>
                    <div class="info-value">
                        <?php 
                        $phuongTien = [
                            'Xe' => 'Xe ô tô',
                            'MayBay' => 'Máy bay',
                            'Tau' => 'Tàu hỏa',
                            'Khac' => 'Khác'
                        ];
                        $ptValue = $tour['phuong_tien'] ?? '';
                        echo !empty($ptValue) ? ($phuongTien[$ptValue] ?? $ptValue) : 'Chưa xác định';
                        ?>
                    </div>
                </div>
                <div>
                    <div class="info-label">👥 Số chỗ tối đa</div>
                    <div class="info-value"><?php echo htmlspecialchars($tour['so_cho_toi_da'] ?? 'Không giới hạn'); ?></div>
                </div>
            </div>
        </div>

        <!-- QR & booking link -->
        <div class="info-card">
            <div class="card-body" style="text-align: center;">
                <h6 style="font-weight: 600; margin-bottom: 20px; color: var(--accent-gold); font-size: 14px;">
                    📱 Đặt tour online
                </h6>
                <?php if ($tourQrPath): ?>
                    <img src="<?php echo $tourQrPath; ?>" alt="QR <?php echo htmlspecialchars($tour['ten_tour']); ?>" 
                         style="max-width: 260px; width: 100%; margin-bottom: 15px; border-radius: 2px;">
                    <div style="margin-bottom: 10px;">
                        <a href="<?php echo $tourQrPath; ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" style="width: 100%;">
                            ⬇️ Tải mã QR
                        </a>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-muted); margin-bottom: 15px; font-size: 13px;">Chưa có mã QR cho tour này.</p>
                <?php endif; ?>
                <a href="index.php?act=tour/generateQr&id=<?php echo $tour['tour_id']; ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                    🔄 Tạo/Cập nhật mã QR
                </a>
                <div style="margin-top: 20px; text-align: left;">
                    <label class="info-label" style="margin-bottom: 8px; display: block;">Link đặt tour</label>
                    <div class="input-group">
                        <input type="text" class="input" id="shareLinkInput" readonly value="<?php echo htmlspecialchars($tourBookingLink); ?>" style="font-size: 11px;">
                        <button class="btn btn-secondary btn-sm" type="button" onclick="copyShareLink('<?php echo htmlspecialchars($tourBookingLink); ?>')">
                            📋
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tour Image -->
        <?php if (!empty($tour['hinh_anh'])): ?>
        <div class="info-card">
            <img src="<?php echo htmlspecialchars($tour['hinh_anh']); ?>" 
                 alt="<?php echo htmlspecialchars($tour['ten_tour']); ?>"
                 style="width: 100%; height: 250px; object-fit: cover; border-radius: 2px 2px 0 0;">
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Tabs Content -->
    <div>
        <!-- Nav Tabs -->
        <div class="nav-tabs">
            <a href="#" class="nav-tab active" data-tab="info">
                ℹ️ Thông tin
            </a>
            <a href="#" class="nav-tab" data-tab="itinerary">
                🗺️ Lịch trình
            </a>
            <a href="#" class="nav-tab" data-tab="schedule">
                📅 Lịch khởi hành
            </a>
            <a href="#" class="nav-tab" data-tab="images">
                🖼️ Hình ảnh
            </a>
        </div>

        <!-- Tab Content -->
        <!-- Tab: Thông tin -->
        <div class="tab-content active" id="info">
            <div class="info-card">
                <div class="card-body">
                    <!-- Mô tả -->
                    <?php if (!empty($tour['mo_ta'])): ?>
                    <div style="margin-bottom: 30px;">
                        <h6 class="section-title">
                            📝 Mô tả tour
                        </h6>
                        <p style="color: var(--text-muted); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($tour['mo_ta'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Bao gồm -->
                    <?php if (!empty($tour['bao_gom'])): ?>
                    <div style="margin-bottom: 30px;">
                        <h6 class="section-title">
                            ✅ Bao gồm
                        </h6>
                        <div style="color: var(--text-muted); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($tour['bao_gom'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Không bao gồm -->
                    <?php if (!empty($tour['khong_bao_gom'])): ?>
                    <div style="margin-bottom: 30px;">
                        <h6 class="section-title">
                            ❌ Không bao gồm
                        </h6>
                        <div style="color: var(--text-muted); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($tour['khong_bao_gom'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Điều kiện hủy -->
                    <?php if (!empty($tour['dieu_kien_huy'])): ?>
                    <div style="margin-bottom: 30px;">
                        <h6 class="section-title">
                            ⚠️ Điều kiện hủy
                        </h6>
                        <div style="color: var(--text-muted); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($tour['dieu_kien_huy'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Lưu ý -->
                    <?php if (!empty($tour['luu_y'])): ?>
                    <div style="margin-bottom: 30px;">
                        <h6 class="section-title">
                            💡 Lưu ý
                        </h6>
                        <div style="color: var(--text-muted); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($tour['luu_y'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Chính sách -->
                    <?php if (!empty($tour['chinh_sach'])): ?>
                    <div>
                        <h6 class="section-title">
                            🛡️ Chính sách
                        </h6>
                        <div style="color: var(--text-muted); line-height: 1.8;"><?php echo nl2br(htmlspecialchars($tour['chinh_sach'])); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Lịch trình -->
        <div class="tab-content" id="itinerary">
            <div class="info-card">
                <div class="card-body">
                    <h6 class="section-title">
                        🗺️ Lịch trình chi tiết
                    </h6>
                    
                    <?php if (!empty($lichTrinhList)): ?>
                        <div class="timeline">
                            <?php foreach ($lichTrinhList as $index => $lt): ?>
                                <div class="timeline-item">
                                    <div class="timeline-badge">
                                        <?php if ($lt['ngay_thu'] == 0): ?>
                                            📍
                                        <?php else: ?>
                                            <?php echo $lt['ngay_thu']; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="info-card" style="margin: 0;">
                                        <div class="card-body">
                                            <h6 style="font-weight: 600; color: var(--accent-gold); margin-bottom: 10px; font-size: 14px;">
                                                <?php if ($lt['ngay_thu'] == 0): ?>
                                                    ✈️ Điểm tập trung: <?php echo htmlspecialchars($lt['dia_diem']); ?>
                                                <?php else: ?>
                                                    🗓️ NGÀY <?php echo $lt['ngay_thu']; ?>: <?php echo htmlspecialchars($lt['dia_diem']); ?>
                                                <?php endif; ?>
                                            </h6>
                                            <div style="color: var(--text-muted); white-space: pre-line; line-height: 1.8; font-size: 13px;">
                                                <?php echo htmlspecialchars($lt['hoat_dong']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📅</div>
                            <p>Chưa có lịch trình nào được thêm</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Lịch khởi hành -->
        <div class="tab-content" id="schedule">
            <div class="info-card">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h6 class="section-title" style="margin: 0;">
                            📅 Lịch khởi hành
                        </h6>
                        <a href="index.php?act=tour/taoLichKhoiHanh&tour_id=<?php echo $tour['tour_id']; ?>" 
                           class="btn btn-primary btn-sm">
                            ➕ Tạo mới
                        </a>
                    </div>
                    
                    <?php if (!empty($lichKhoiHanhList)): ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                            <?php foreach ($lichKhoiHanhList as $lk): ?>
                                <div class="schedule-card">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                        <span class="status-badge <?php 
                                            echo match($lk['trang_thai']) {
                                                'SapKhoiHanh' => 'badge-info',
                                                'DangChay' => 'badge-success',
                                                'HoanThanh' => 'badge-secondary',
                                                default => 'badge-secondary'
                                            };
                                        ?>">
                                            <?php
                                            $statusLabels = [
                                                'SapKhoiHanh' => 'Sắp khởi hành',
                                                'DangChay' => 'Đang chạy',
                                                'HoanThanh' => 'Hoàn thành'
                                            ];
                                            echo $statusLabels[$lk['trang_thai']] ?? $lk['trang_thai'];
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div style="margin-bottom: 10px; font-size: 13px;">
                                        <span style="color: var(--accent-gold);">📅 Khởi hành:</span>
                                        <strong style="color: var(--text-light);">
                                            <?php echo $lk['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($lk['ngay_khoi_hanh'])) : 'N/A'; ?>
                                        </strong>
                                        <span style="color: var(--text-muted); margin-left: 5px;">• <?php echo $lk['gio_xuat_phat'] ?? 'N/A'; ?></span>
                                    </div>
                                    
                                    <div style="margin-bottom: 10px; font-size: 13px;">
                                        <span style="color: var(--accent-gold);">✅ Kết thúc:</span>
                                        <strong style="color: var(--text-light);">
                                            <?php echo $lk['ngay_ket_thuc'] ? date('d/m/Y', strtotime($lk['ngay_ket_thuc'])) : 'N/A'; ?>
                                        </strong>
                                        <span style="color: var(--text-muted); margin-left: 5px;">• <?php echo $lk['gio_ket_thuc'] ?? 'N/A'; ?></span>
                                    </div>
                                    
                                    <div style="margin-bottom: 15px; font-size: 13px;">
                                        <span style="color: var(--accent-gold);">📍 Điểm tập trung:</span>
                                        <span style="color: var(--text-light);"><?php echo htmlspecialchars($lk['diem_tap_trung'] ?? 'N/A'); ?></span>
                                    </div>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="color: var(--text-muted); font-size: 12px;">
                                            👥 <?php echo $lk['so_cho'] ?? 50; ?> chỗ
                                        </span>
                                        <a href="index.php?act=tour/chiTietLichKhoiHanh&id=<?php echo $lk['id']; ?>&tour_id=<?php echo $tour['tour_id']; ?>" 
                                           class="btn btn-secondary btn-sm">
                                            👁️ Chi tiết
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📅</div>
                            <p>Chưa có lịch khởi hành nào</p>
                            <a href="index.php?act=tour/taoLichKhoiHanh&tour_id=<?php echo $tour['tour_id']; ?>" 
                               class="btn btn-primary" style="margin-top: 15px;">
                                ➕ Tạo lịch khởi hành đầu tiên
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab: Hình ảnh -->
        <div class="tab-content" id="images">
            <div class="info-card">
                <div class="card-body">
                    <h6 class="section-title">
                        🖼️ Thư viện hình ảnh
                    </h6>
                    
                    <?php if (!empty($hinhAnhList)): ?>
                        <div class="image-gallery">
                            <?php foreach ($hinhAnhList as $anh): ?>
                                <div class="image-gallery-item">
                                    <img src="<?php echo htmlspecialchars($anh['url_anh']); ?>" 
                                         alt="<?php echo htmlspecialchars($anh['mo_ta'] ?? ''); ?>">
                                    <?php if (!empty($anh['mo_ta'])): ?>
                                        <div style="padding: 10px; background: rgba(45, 45, 45, 0.9); position: absolute; bottom: 0; left: 0; right: 0;">
                                            <small style="color: var(--text-muted); font-size: 11px;"><?php echo htmlspecialchars($anh['mo_ta']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🖼️</div>
                            <p>Chưa có hình ảnh nào</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active from all tabs and contents
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    function copyShareLink(link) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(link)
                .then(() => alert('Đã sao chép link đặt tour.'))
                .catch(() => alert('Không thể sao chép link.'));
        } else {
            const tempInput = document.createElement('input');
            tempInput.value = link;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('Đã sao chép link đặt tour.');
        }
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
