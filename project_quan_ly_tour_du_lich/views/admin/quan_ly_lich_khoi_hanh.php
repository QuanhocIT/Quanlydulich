<?php
$pageTitle = 'Quản lý Lịch Khởi Hành';
$currentPage = 'lichKhoiHanh';
ob_start();
?>

<style>
    .page-header-section {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 32px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        display: flex;
        align-items: center;
        gap: 22px;
        overflow: hidden;
    }
    .page-header-glow {
        position: absolute;
        top: 0; left: -60%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: phglow 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }
    @keyframes phglow {
        0% { left: -60%; }
        100% { left: 100%; }
    }
    .page-header-avatar {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        z-index: 2;
        flex-shrink: 0;
    }
    .page-header-body {
        flex: 1;
        z-index: 2;
    }
    .page-header-section h1 {
        font-size: 1.7rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 6px;
        color: #ffe082;
        text-shadow: 0 2px 8px #2d2d2d;
    }
    .page-header-section p {
        color: #fffde7;
        font-size: 1rem;
        text-shadow: 0 1px 4px #2d2d2d;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-left: 4px solid;
        border-radius: 8px;
        padding: 20px 24px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.15);
    }

    .stat-card.border-primary { border-left-color: var(--accent-gold); }
    .stat-card.border-info { border-left-color: #0dcaf0; }
    .stat-card.border-success { border-left-color: #10b981; }
    .stat-card.border-secondary { border-left-color: #6c757d; }
    .stat-card.border-warning { border-left-color: var(--accent-gold); }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        transition: all 0.3s;
        flex-shrink: 0;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-icon.bg-primary { background: rgba(212,175,55,0.13); color: var(--accent-gold); }
    .stat-icon.bg-info { background: rgba(13,202,240,0.13); color: #0dcaf0; }
    .stat-icon.bg-success { background: rgba(16,185,129,0.13); color: #10b981; }
    .stat-icon.bg-secondary { background: rgba(108,117,125,0.13); color: #6c757d; }
    .stat-icon.bg-warning { background: rgba(212,175,55,0.13); color: var(--accent-gold); }

    .stat-card:hover .stat-icon.bg-primary { background: var(--accent-gold); color: var(--primary-dark); }
    .stat-card:hover .stat-icon.bg-info { background: #0dcaf0; color: var(--primary-dark); }
    .stat-card:hover .stat-icon.bg-success { background: #10b981; color: var(--primary-dark); }
    .stat-card:hover .stat-icon.bg-warning { background: var(--accent-gold); color: var(--primary-dark); }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #ffd700;
        line-height: 1;
    }

    .stat-label {
        font-size: 11px;
        color: var(--text-muted);
        letter-spacing: 0.8px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        padding: 22px 28px;
        margin-bottom: 28px;
        backdrop-filter: blur(10px);
    }

    .filter-row {
        display: grid;
        grid-template-columns: 3fr 2fr 2fr 2fr 3fr;
        gap: 15px;
        align-items: end;
    }

    .schedule-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
        gap: 20px;
    }

    .schedule-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
        display: flex;
        gap: 20px;
    }

    .schedule-card:hover {
        border-color: var(--accent-gold);
        transform: translateY(-5px);
    }

    .date-badge {
        background: rgba(212, 175, 55, 0.1);
        border: 2px solid rgba(212, 175, 55, 0.3);
        border-radius: 2px;
        padding: 15px;
        text-align: center;
        min-width: 80px;
        height: fit-content;
    }

    .date-day {
        font-size: 28px;
        font-weight: 700;
        color: var(--accent-gold);
        line-height: 1;
        margin-bottom: 5px;
    }

    .date-month {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .tour-badge {
        background: rgba(212, 175, 55, 0.2);
        color: var(--accent-gold);
        padding: 4px 10px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        display: inline-block;
        margin-bottom: 10px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .status-SapKhoiHanh {
        background: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }

    .status-DangChay {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .status-HoanThanh {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }

    .status-ChoPhanBo {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .warning-badge {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        padding: 4px 8px;
        border-radius: 2px;
        font-size: 10px;
        font-weight: 600;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .tour-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin: 15px 0;
        font-size: 12px;
        color: var(--text-muted);
    }

    .tour-info-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tour-info-item strong {
        color: var(--text-light);
        margin-right: 5px;
    }

    .tour-title {
        font-size: 18px;
        font-weight: 600;
        margin: 10px 0;
        letter-spacing: 0.5px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
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

    @media (max-width: 1200px) {
        .schedule-grid {
            grid-template-columns: 1fr;
        }
        .filter-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div class="page-header-glow"></div>
    <div class="page-header-avatar">✈️</div>
    <div class="page-header-body">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
            <div>
                <h1>Quản lý Lịch Khởi Hành</h1>
                <p>Theo dõi và quản lý tất cả các lịch khởi hành tour</p>
            </div>
            <a href="index.php?act=admin/dashboard" class="btn btn-secondary" style="z-index:2;">
                ← Dashboard
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

<!-- Statistics Cards -->
<?php if (isset($lichKhoiHanhList) && !empty($lichKhoiHanhList)): ?>
    <?php
    $totalSchedules = count($lichKhoiHanhList);
    $upcomingSchedules = count(array_filter($lichKhoiHanhList, fn($l) => $l['trang_thai'] === 'SapKhoiHanh' && ($l['so_nhan_su'] ?? 0) > 0));
    $ongoingSchedules = count(array_filter($lichKhoiHanhList, fn($l) => $l['trang_thai'] === 'DangChay'));
    $completedSchedules = count(array_filter($lichKhoiHanhList, fn($l) => $l['trang_thai'] === 'HoanThanh'));
    $choPhanBo = count(array_filter($lichKhoiHanhList, fn($l) => ($l['so_nhan_su'] ?? 0) == 0));
    ?>
    <div class="stats-grid">
        <div class="stat-card border-primary">
            <div><div class="stat-value"><?php echo $totalSchedules; ?></div><div class="stat-label">Tổng số lịch</div></div>
            <div class="stat-icon bg-primary">📅</div>
        </div>
        <div class="stat-card border-info">
            <div><div class="stat-value" style="color:#0dcaf0"><?php echo $upcomingSchedules; ?></div><div class="stat-label">Sắp khởi hành</div></div>
            <div class="stat-icon bg-info">⏰</div>
        </div>
        <div class="stat-card border-success">
            <div><div class="stat-value" style="color:#10b981"><?php echo $ongoingSchedules; ?></div><div class="stat-label">Đang chạy</div></div>
            <div class="stat-icon bg-success">▶</div>
        </div>
        <div class="stat-card border-secondary">
            <div><div class="stat-value" style="color:#adb5bd"><?php echo $completedSchedules; ?></div><div class="stat-label">Hoàn thành</div></div>
            <div class="stat-icon bg-secondary">✓</div>
        </div>
        <div class="stat-card border-warning">
            <div><div class="stat-value" style="color:#ffd700"><?php echo $choPhanBo; ?></div><div class="stat-label">Đang chờ phân bổ</div></div>
            <div class="stat-icon bg-warning">⏳</div>
        </div>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="filter-section">
    <form method="get" action="index.php">
        <input type="hidden" name="act" value="lichKhoiHanh/index">
        <div class="filter-row">
            <div class="form-group">
                <label>Tìm kiếm</label>
                <div style="position: relative;">
                    <input type="text" name="search" class="form-group input" 
                           placeholder="Tên tour, điểm tập trung..." 
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                           style="padding-left: 35px;">
                    <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">🔍</span>
                </div>
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="trang_thai" class="form-group select">
                    <option value="">Tất cả</option>
                    <option value="ChoPhanBo" <?php echo (isset($filters['trang_thai']) && $filters['trang_thai'] === 'ChoPhanBo') ? 'selected' : ''; ?>>Đang chờ phân bổ</option>
                    <option value="SapKhoiHanh" <?php echo (isset($filters['trang_thai']) && $filters['trang_thai'] === 'SapKhoiHanh') ? 'selected' : ''; ?>>Sắp khởi hành</option>
                    <option value="DangChay" <?php echo (isset($filters['trang_thai']) && $filters['trang_thai'] === 'DangChay') ? 'selected' : ''; ?>>Đang chạy</option>
                    <option value="HoanThanh" <?php echo (isset($filters['trang_thai']) && $filters['trang_thai'] === 'HoanThanh') ? 'selected' : ''; ?>>Hoàn thành</option>
                </select>
            </div>
            <div class="form-group">
                <label>Từ ngày</label>
                <input type="date" name="tu_ngay" class="form-group input" value="<?php echo htmlspecialchars($filters['tu_ngay'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Đến ngày</label>
                <input type="date" name="den_ngay" class="form-group input" value="<?php echo htmlspecialchars($filters['den_ngay'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    🔍 Lọc dữ liệu
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Schedule List -->
<?php if (isset($lichKhoiHanhList) && !empty($lichKhoiHanhList)): ?>
    <div class="schedule-grid">
        <?php foreach ($lichKhoiHanhList as $lich): ?>
            <div class="schedule-card">
                <!-- Date Badge -->
                <div class="date-badge">
                    <div class="date-day">
                        <?php echo $lich['ngay_khoi_hanh'] ? date('d', strtotime($lich['ngay_khoi_hanh'])) : '--'; ?>
                    </div>
                    <div class="date-month">
                        <?php 
                        if ($lich['ngay_khoi_hanh']) {
                            $months = ['', 'THG 1', 'THG 2', 'THG 3', 'THG 4', 'THG 5', 'THG 6', 
                                      'THG 7', 'THG 8', 'THG 9', 'THG 10', 'THG 11', 'THG 12'];
                            echo $months[(int)date('n', strtotime($lich['ngay_khoi_hanh']))];
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>
                </div>

                <!-- Content -->
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                        <div>
                            <span class="tour-badge">#<?php echo $lich['id']; ?></span>
                            <h5 class="tour-title"><?php echo htmlspecialchars($lich['ten_tour'] ?? 'N/A'); ?></h5>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="status-badge <?php 
                                if (($lich['so_nhan_su'] ?? 0) == 0) {
                                    echo 'status-ChoPhanBo';
                                } else {
                                    echo 'status-' . htmlspecialchars($lich['trang_thai']);
                                }
                            ?>">
                                <?php
                                if (($lich['so_nhan_su'] ?? 0) == 0) {
                                    echo 'Đang chờ phân bổ';
                                } else {
                                    $statusLabels = [
                                        'SapKhoiHanh' => 'Sắp khởi hành',
                                        'DangChay' => 'Đang chạy',
                                        'HoanThanh' => 'Hoàn thành'
                                    ];
                                    echo $statusLabels[$lich['trang_thai']] ?? $lich['trang_thai'];
                                }
                                ?>
                            </span>
                            <?php
                            // Kiểm tra và hiển thị cảnh báo
                            $coCanhBao = false;
                            $loaiCanhBao = [];
                            
                            if (($lich['so_nhan_su'] ?? 0) == 0) {
                                $coCanhBao = true;
                                $loaiCanhBao[] = 'HDV';
                            }
                            
                            if (($lich['so_dich_vu'] ?? 0) == 0) {
                                $coCanhBao = true;
                                $loaiCanhBao[] = 'DV';
                            }
                            
                            $coTrungLichHDV = ($lich['coTrungLichHDV'] ?? false);
                            if ($coTrungLichHDV) {
                                $coCanhBao = true;
                                $loaiCanhBao[] = 'HDV';
                            }
                            
                            $canhBaoGanNgay = false;
                            if (!empty($lich['ngay_khoi_hanh']) && in_array($lich['trang_thai'], ['SapKhoiHanh', 'DangChay'])) {
                                $ngayKhoiHanh = new DateTime($lich['ngay_khoi_hanh']);
                                $ngayHienTai = new DateTime();
                                $soNgayConLai = $ngayHienTai->diff($ngayKhoiHanh)->days;
                                
                                if ($soNgayConLai <= 7 && $soNgayConLai >= 0) {
                                    $canhBaoGanNgay = true;
                                }
                            }
                            
                            if ($coCanhBao || $canhBaoGanNgay):
                            ?>
                                <span class="warning-badge" title="<?php 
                                    $messages = [];
                                    if (in_array('HDV', $loaiCanhBao)) {
                                        $messages[] = 'Thiếu nhân sự';
                                    }
                                    if (in_array('DV', $loaiCanhBao)) {
                                        $messages[] = 'Thiếu dịch vụ';
                                    }
                                    if ($coTrungLichHDV) {
                                        $soLichTrung = $lich['soLichTrungHDV'] ?? 0;
                                        $messages[] = 'HDV bị trùng lịch (' . $soLichTrung . ' lịch)';
                                    }
                                    if ($canhBaoGanNgay) {
                                        $messages[] = 'Gần ngày khởi hành';
                                    }
                                    echo implode(', ', $messages);
                                ?>">
                                    ▲
                                    <?php 
                                    if (count($loaiCanhBao) > 0) {
                                        echo implode(' + ', $loaiCanhBao);
                                    }
                                    if ($canhBaoGanNgay) {
                                        if (count($loaiCanhBao) > 0) echo ' + ';
                                        echo '!';
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tour-info-grid">
                        <div class="tour-info-item">
                            <strong>📅 Khởi hành:</strong>
                            <?php echo $lich['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])) : 'N/A'; ?>
                        </div>
                        <div class="tour-info-item">
                            <strong>🕐 Giờ:</strong>
                            <?php echo $lich['gio_xuat_phat'] ?? 'N/A'; ?>
                        </div>
                        <div class="tour-info-item">
                            <strong>✓ Kết thúc:</strong>
                            <?php echo $lich['ngay_ket_thuc'] ? date('d/m/Y', strtotime($lich['ngay_ket_thuc'])) : 'N/A'; ?>
                        </div>
                        <div class="tour-info-item">
                            <strong>🕐 Giờ:</strong>
                            <?php echo $lich['gio_ket_thuc'] ?? 'N/A'; ?>
                        </div>
                        <div class="tour-info-item" style="grid-column: 1 / -1;">
                            <strong>📍 Điểm tập trung:</strong>
                            <?php echo htmlspecialchars($lich['diem_tap_trung'] ?? 'Chưa xác định'); ?>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: var(--text-light); padding: 6px 12px; border-radius: 2px; font-size: 11px;">
                            👥 <?php echo $lich['so_cho'] ?? 50; ?> chỗ
                        </span>
                        <a href="index.php?act=lichKhoiHanh/chiTiet&id=<?php echo $lich['id']; ?>" 
                           class="btn btn-secondary btn-sm">
                            👁️ Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card" style="padding: 60px 20px; text-align: center; color: var(--text-muted);">
        <div class="empty-state-icon">📅</div>
        <h4 style="margin-bottom: 15px;">Chưa có lịch khởi hành nào</h4>
        <p>Hãy tạo lịch khởi hành đầu tiên để bắt đầu quản lý tour</p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
