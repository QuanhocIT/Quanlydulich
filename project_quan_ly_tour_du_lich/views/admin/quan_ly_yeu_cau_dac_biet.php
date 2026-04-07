<?php
$pageTitle = 'Quản lý Yêu cầu đặc biệt';
$currentPage = 'yeuCauDacBiet';
ob_start();
?>
<style>
        .stat-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .stat-card.border-start {
            border-left-width: 4px !important;
        }
        .badge-priority {
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
            border-radius: 50px;
        }
        .table > :not(caption) > * > * {
            vertical-align: middle;
        }
        .request-note {
            max-width: 320px;
            white-space: pre-line;
        }
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card-body {
            color: var(--text-light);
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
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-danger {
            background: rgba(220, 53, 69, 0.3) !important;
            color: #dc3545 !important;
        }
        .bg-warning {
            background: rgba(255, 193, 7, 0.3) !important;
            color: #ffc107 !important;
        }
        .bg-info {
            background: rgba(0, 123, 255, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-secondary {
            background: rgba(108, 117, 125, 0.3) !important;
            color: #adb5bd !important;
        }
        .bg-success {
            background: rgba(40, 167, 69, 0.3) !important;
            color: #5cb85c !important;
        }
        .bg-primary {
            background: rgba(13, 110, 253, 0.3) !important;
            color: #4da3ff !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .text-primary {
            color: #4da3ff !important;
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
        .btn-outline-primary {
            background: transparent;
            color: #4da3ff;
            border: 1px solid rgba(13, 110, 253, 0.5);
        }
        .btn-outline-primary:hover {
            background: rgba(13, 110, 253, 0.3);
        }
        .btn-light {
            background: rgba(248, 249, 250, 0.2);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-light:hover {
            background: rgba(248, 249, 250, 0.3);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .table {
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .modal-content {
            background: rgba(45, 45, 45, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .modal-title {
            color: var(--text-light);
        }
        .btn-close {
            filter: invert(1);
        }
    </style>

<div style="padding: 20px;">
<?php
$priorityMap = [
    'khan_cap' => ['label' => 'Khẩn cấp', 'badge' => 'danger'],
    'cao' => ['label' => 'Cao', 'badge' => 'warning'],
    'trung_binh' => ['label' => 'Trung bình', 'badge' => 'info'],
    'thap' => ['label' => 'Thấp', 'badge' => 'secondary'],
];
$statusMap = [
    'moi' => ['label' => 'Mới', 'badge' => 'secondary'],
    'dang_xu_ly' => ['label' => 'Đang xử lý', 'badge' => 'primary'],
    'da_giai_quyet' => ['label' => 'Đã giải quyết', 'badge' => 'success'],
    'khong_the_thuc_hien' => ['label' => 'Không thể thực hiện', 'badge' => 'danger'],
];
$stats = $stats ?? [];
$totalRequests = (int)(($stats['khan_cap'] ?? 0) + ($stats['cao'] ?? 0) + ($stats['trung_binh'] ?? 0) + ($stats['thap'] ?? 0));
?>
    <div class="page-header-section" style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
                    <i class="bi bi-clipboard-check" style="color: var(--accent-gold);"></i> Quản lý yêu cầu đặc biệt
                </h1>
                <p style="color: var(--text-muted); margin: 8px 0 0 0;">Theo dõi và xử lý các yêu cầu cá nhân của khách hàng</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
                    <i class="bi bi-plus-lg"></i> Tạo yêu cầu
                </button>
                <a href="index.php?act=admin/dashboard" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert" style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert" style="display: flex; justify-content: space-between; align-items: center;">
                <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stat-card border-start border-danger border-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Khẩn cấp</p>
                        <h3 class="mb-0 text-danger"><?php echo (int)($stats['khan_cap'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-start border-warning border-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Đang xử lý</p>
                        <h3 class="mb-0 text-warning"><?php echo (int)($stats['trang_thai_dang_xu_ly'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-start border-success border-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Đã giải quyết</p>
                        <h3 class="mb-0 text-success"><?php echo (int)($stats['trang_thai_da_giai_quyet'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-start border-secondary border-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Tổng yêu cầu</p>
                        <h3 class="mb-0"><?php echo $totalRequests; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <form class="card shadow-sm mb-4" method="GET" action="">
            <input type="hidden" name="act" value="admin/yeuCauDacBiet">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Từ khóa</label>
                        <input type="text" name="keyword" class="form-control" placeholder="Tên khách, tour, số điện thoại" value="<?php echo htmlspecialchars($filters['keyword'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Ưu tiên</label>
                        <select name="muc_do_uu_tien" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($priorityMap as $key => $info): ?>
                                <option value="<?php echo $key; ?>" <?php echo (($filters['muc_do_uu_tien'] ?? '') === $key) ? 'selected' : ''; ?>><?php echo $info['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="trang_thai" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($statusMap as $key => $info): ?>
                                <option value="<?php echo $key; ?>" <?php echo (($filters['trang_thai'] ?? '') === $key) ? 'selected' : ''; ?>><?php echo $info['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Loại yêu cầu</label>
                        <select name="loai_yeu_cau" class="form-select">
                            <option value="">Tất cả</option>
                            <?php $types = ['an_uong' => 'Ăn uống', 'suc_khoe' => 'Sức khỏe', 'di_chuyen' => 'Di chuyển', 'phong_o' => 'Phòng ở', 'hoat_dong' => 'Hoạt động', 'khac' => 'Khác'];
                            foreach ($types as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo (($filters['loai_yeu_cau'] ?? '') === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tour</label>
                        <select name="tour_id" class="form-select">
                            <option value="0">Tất cả tour</option>
                            <?php foreach ($tourList as $tour): ?>
                                <option value="<?php echo $tour['tour_id']; ?>" <?php echo ((int)($filters['tour_id'] ?? 0) === (int)$tour['tour_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="index.php?act=admin/yeuCauDacBiet" class="btn btn-light w-100">Đặt lại</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (!empty($requests)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Khách hàng</th>
                                    <th>Tour</th>
                                    <th>Chi tiết yêu cầu</th>
                                    <th>Ưu tiên</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request):
                                    $priorityInfo = $priorityMap[$request['muc_do_uu_tien'] ?? 'trung_binh'] ?? $priorityMap['trung_binh'];
                                    $statusInfo = $statusMap[$request['trang_thai'] ?? 'moi'] ?? $statusMap['moi'];
                                    $historyData = htmlspecialchars(json_encode($histories[$request['id']] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $request['id']; ?></strong><br>
                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($request['ngay_tao'] ?? 'now')); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['khach_ten'] ?? 'N/A'); ?></strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars($request['khach_phone'] ?? ''); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($request['khach_email'] ?? ''); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?php echo htmlspecialchars($request['ten_tour'] ?? 'Chưa rõ'); ?></div>
                                            <small class="text-muted">Khởi hành: <?php echo !empty($request['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($request['ngay_khoi_hanh'])) : 'N/A'; ?></small>
                                        </td>
                                        <td class="request-note">
                                            <div class="fw-semibold mb-1"><?php echo htmlspecialchars($request['tieu_de'] ?? 'Yêu cầu'); ?></div>
                                            <div class="text-muted"><?php echo nl2br(htmlspecialchars($request['mo_ta'] ?? '')); ?></div>
                                            <?php if (!empty($request['ghi_chu_hdv'])): ?>
                                                <div class="mt-2 text-primary small"><i class="bi bi-sticky"></i> <?php echo nl2br(htmlspecialchars($request['ghi_chu_hdv'])); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $priorityInfo['badge']; ?> badge-priority"><?php echo $priorityInfo['label']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $statusInfo['badge']; ?>"><?php echo $statusInfo['label']; ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#updateModal"
                                                data-id="<?php echo $request['id']; ?>"
                                                data-trangthai="<?php echo htmlspecialchars($request['trang_thai']); ?>"
                                                data-uutien="<?php echo htmlspecialchars($request['muc_do_uu_tien']); ?>"
                                                data-ghichu="<?php echo htmlspecialchars($request['ghi_chu_hdv'] ?? '', ENT_QUOTES); ?>"
                                                data-khach="<?php echo htmlspecialchars($request['khach_ten'] ?? '', ENT_QUOTES); ?>"
                                                data-tour="<?php echo htmlspecialchars($request['ten_tour'] ?? '', ENT_QUOTES); ?>"
                                                data-history="<?php echo $historyData; ?>">
                                                <i class="bi bi-pencil-square me-1"></i>Cập nhật
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inboxes text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">Không tìm thấy yêu cầu nào phù hợp với bộ lọc hiện tại.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal tạo mới yêu cầu -->
    <div class="modal fade" id="createRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="index.php?act=admin/themYeuCauDacBiet">
                    <div class="modal-header">
                        <h5 class="modal-title">Tạo yêu cầu đặc biệt mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Booking / Khách hàng</label>
                                <select name="booking_id" class="form-select" required>
                                    <option value="">-- Chọn booking --</option>
                                    <?php if (!empty($bookingList)): ?>
                                        <?php foreach ($bookingList as $bk): ?>
                                            <?php
                                                $label = sprintf(
                                                    '#%d - %s | %s | KH: %s (%s)',
                                                    $bk['booking_id'],
                                                    !empty($bk['ten_tour']) ? $bk['ten_tour'] : 'Tour',
                                                    !empty($bk['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($bk['ngay_khoi_hanh'])) : 'N/A',
                                                    $bk['ho_ten'] ?? 'N/A',
                                                    $bk['so_dien_thoai'] ?? ''
                                                );
                                            ?>
                                            <option value="<?php echo (int)$bk['booking_id']; ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loại yêu cầu</label>
                                <select name="loai_yeu_cau" class="form-select">
                                    <?php $types = ['an_uong' => 'Ăn uống', 'suc_khoe' => 'Sức khỏe', 'di_chuyen' => 'Di chuyển', 'phong_o' => 'Phòng ở', 'hoat_dong' => 'Hoạt động', 'khac' => 'Khác'];
                                    foreach ($types as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mức ưu tiên</label>
                                <select name="muc_do_uu_tien" class="form-select">
                                    <?php foreach ($priorityMap as $key => $info): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $info['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" class="form-select">
                                    <?php foreach ($statusMap as $key => $info): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $info['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" name="tieu_de" class="form-control" placeholder="Tiêu đề yêu cầu (tuỳ chọn)">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Nội dung yêu cầu</label>
                                <textarea name="mo_ta" rows="4" class="form-control" placeholder="Mô tả chi tiết yêu cầu đặc biệt của khách"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ghi chú xử lý (nếu có)</label>
                                <textarea name="ghi_chu_hdv" rows="3" class="form-control" placeholder="Ghi chú nội bộ cho HDV / bộ phận vận hành"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Lưu yêu cầu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="index.php?act=admin/capNhatYeuCauDacBiet">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Cập nhật yêu cầu</h5>
                            <div class="small text-muted" id="modalSubTitle"></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="yeu_cau_id" id="modalYeuCauId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mức ưu tiên</label>
                                <select name="muc_do_uu_tien" id="modalUuTien" class="form-select" required>
                                    <?php foreach ($priorityMap as $key => $info): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $info['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" id="modalTrangThai" class="form-select" required>
                                    <?php foreach ($statusMap as $key => $info): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $info['label']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ghi chú xử lý</label>
                                <textarea name="ghi_chu_hdv" id="modalGhiChu" rows="4" class="form-control" placeholder="Ghi lại cách xử lý, thông tin đã trao đổi với khách..."></textarea>
                            </div>
                        </div>
                        <hr>
                        <div>
                            <h6 class="text-muted">Lịch sử cập nhật</h6>
                            <div id="historyList" class="small" style="max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Custom modal handling for Aventura theme
        const modalElement = document.getElementById('updateModal');
        if (modalElement) {
            modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            const id = button.getAttribute('data-id');
            const trangThai = button.getAttribute('data-trangthai');
            const uuTien = button.getAttribute('data-uutien');
            const ghiChu = button.getAttribute('data-ghichu');
            const khach = button.getAttribute('data-khach');
            const tour = button.getAttribute('data-tour');
            const history = button.getAttribute('data-history');

            modalElement.querySelector('#modalYeuCauId').value = id;
            modalElement.querySelector('#modalTrangThai').value = trangThai;
            modalElement.querySelector('#modalUuTien').value = uuTien;
            modalElement.querySelector('#modalGhiChu').value = ghiChu ?? '';
            modalElement.querySelector('#modalSubTitle').innerText = `${khach || ''} - ${tour || ''}`;

            const historyWrapper = modalElement.querySelector('#historyList');
            historyWrapper.innerHTML = '';
            try {
                const historyData = history ? JSON.parse(history) : [];
                if (historyData.length === 0) {
                    historyWrapper.innerHTML = '<p class="text-muted mb-0">Chưa có lịch sử.</p>';
                } else {
                    historyData.forEach(item => {
                        const time = item.ngay_thuc_hien ? new Date(item.ngay_thuc_hien).toLocaleString('vi-VN') : '';
                        const note = item.noi_dung || '';
                        const actor = item.ho_ten || 'Hệ thống';
                        historyWrapper.insertAdjacentHTML('beforeend',
                            `<div class="mb-2"><strong>${actor}</strong> • <span class="text-muted">${time}</span><br><span>${note}</span></div>`
                        );
                    });
                }
            } catch (e) {
                historyWrapper.innerHTML = '<p class="text-muted mb-0">Không thể tải lịch sử.</p>';
            }
            });
        }
    </script>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
