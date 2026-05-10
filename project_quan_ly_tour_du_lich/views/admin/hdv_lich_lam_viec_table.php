<?php
$pageTitle = 'Lịch làm việc HDV - Dạng bảng';
$currentPage = 'nhanSu';
ob_start();
?>
<style>
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: rgba(13, 110, 253, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .card-body {
            color: var(--text-light);
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
        .table-bordered {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table-light {
            background: rgba(45, 45, 45, 0.7) !important;
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
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-info {
            background: rgba(0, 123, 255, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-success {
            background: rgba(40, 167, 69, 0.3) !important;
            color: #5cb85c !important;
        }
        .bg-warning {
            background: rgba(255, 193, 7, 0.3) !important;
            color: #ffc107 !important;
        }
        .bg-dark {
            background: rgba(33, 37, 41, 0.3) !important;
            color: #adb5bd !important;
        }
        .bg-primary {
            background: rgba(13, 110, 253, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-danger {
            background: rgba(220, 53, 69, 0.3) !important;
            color: #dc3545 !important;
        }
        .bg-secondary {
            background: rgba(108, 117, 125, 0.3) !important;
            color: #adb5bd !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
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
        .btn-light {
            background: rgba(248, 249, 250, 0.2);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-light:hover {
            background: rgba(248, 249, 250, 0.3);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .btn-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(108, 117, 125, 0.5);
        }
        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.5);
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
        code {
            background: rgba(45, 45, 45, 0.5);
            color: var(--text-light);
            padding: 2px 6px;
            border-radius: 4px;
        }
        pre {
            background: rgba(45, 45, 45, 0.5);
            color: var(--text-light);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>

<div style="padding: 20px;">
    <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; ?>
        <div class="alert alert-<?php echo htmlspecialchars($f['type']); ?>" style="display: flex; justify-content: space-between; align-items: center;">
            <span><?php echo htmlspecialchars($f['message']); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">&times;</button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-calendar-check" style="color: var(--accent-gold);"></i> Lịch làm việc tất cả HDV
        </h1>
    </div>

    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h5 style="margin: 0; color: var(--text-light);"><i class="bi bi-calendar3"></i> Lịch làm việc tất cả HDV</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                <i class="bi bi-plus-circle"></i> Thêm lịch
            </button>
        </div>
            <div class="card-body">
                <!-- Bộ lọc -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="filterHDV" onchange="filterTable()">
                            <option value="">Tất cả HDV</option>
                            <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                            <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                            </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="filterTrangThai" onchange="filterTable()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="ChoXacNhan">Chờ xác nhận</option>
                            <option value="DaXacNhan">Đã xác nhận</option>
                            <option value="SapKhoiHanh">Sắp khởi hành</option>
                            <option value="DangChay">Đang chạy</option>
                            <option value="HoanThanh">Hoàn thành</option>
                            <option value="Huy">Hủy</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="month" class="form-control" id="filterThang" onchange="filterTable()" 
                               value="<?php echo date('Y-m'); ?>">
                    </div>
                </div>

                <!-- Bảng lịch làm việc -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">HDV</th>
                                <th width="25%">Tour</th>
                                <th width="12%">Ngày KH</th>
                                <th width="12%">Ngày KT</th>
                                <th width="10%">Trạng thái LKH</th>
                                <th width="10%">Trạng thái PB</th>
                                <th width="6%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleTableBody">
                            <?php if (!empty($lich_lam_viec)): 
                                $stt = 1;
                                foreach($lich_lam_viec as $lich): 
                            ?>
                            <tr class="schedule-row" 
                                data-hdv="<?php echo $lich['nhan_su_id']; ?>"
                                data-trangthai="<?php echo $lich['trang_thai']; ?>"
                                data-thang="<?php echo date('Y-m', strtotime($lich['ngay_khoi_hanh'])); ?>">
                                <td><?php echo $stt++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($lich['ho_ten'] ?? 'N/A'); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-person-badge"></i> 
                                        <?php echo $lich['vai_tro'] ?? 'HDV'; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($lich['ten_tour'] ?? 'Chưa có thông tin tour'); ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-event"></i>
                                    <?php echo date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])); ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-check"></i>
                                    <?php echo date('d/m/Y', strtotime($lich['ngay_ket_thuc'])); ?>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = 'secondary';
                                    switch($lich['trang_thai']) {
                                        case 'SapKhoiHanh': $badge_class = 'info'; break;
                                        case 'DangChay': $badge_class = 'success'; break;
                                        case 'HoanThanh': $badge_class = 'dark'; break;
                                        case 'DaXacNhan': $badge_class = 'primary'; break;
                                        case 'ChoXacNhan': $badge_class = 'warning'; break;
                                        case 'Huy': $badge_class = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <?php echo $lich['trang_thai']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $pb_badge = 'secondary';
                                    switch($lich['pb_trang_thai'] ?? 'ChoXacNhan') {
                                        case 'DaXacNhan': $pb_badge = 'success'; break;
                                        case 'ChoXacNhan': $pb_badge = 'warning'; break;
                                        case 'TuChoi': $pb_badge = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $pb_badge; ?>">
                                        <?php echo $lich['pb_trang_thai'] ?? 'ChoXacNhan'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?act=admin/chi_tiet_lich_khoi_hanh&id=<?php echo $lich['id']; ?>" 
                                       class="btn btn-sm btn-primary" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Chưa có lịch làm việc nào</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Thống kê -->
                <?php if (!empty($lich_lam_viec)): ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Tổng số lịch:</strong> <?php echo count($lich_lam_viec); ?> lịch làm việc
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Debug info -->
        <div class="card mt-3">
            <div class="card-header">
                <strong>Debug Info</strong>
            </div>
            <div class="card-body">
                <p><strong>API Endpoint:</strong> <code>index.php?act=admin/hdv_get_schedule</code></p>
                <button class="btn btn-sm btn-secondary" onclick="testAPI()">Test API</button>
                <pre id="apiResult" class="mt-2 bg-light p-2" style="max-height: 300px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>

    <!-- Modal: Thêm lịch -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm lịch làm việc</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="index.php?act=admin/hdv_add_schedule">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">HDV *</label>
                            <select name="hdv_id" class="form-select" required>
                                <option value="">-- Chọn HDV --</option>
                                <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                                <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                    <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tour *</label>
                            <select name="tour_id" class="form-select" required>
                                <option value="">-- Chọn tour --</option>
                                <?php if (!empty($tours)): foreach($tours as $tour): ?>
                                <option value="<?php echo $tour['tour_id']; ?>">
                                    <?php echo htmlspecialchars($tour['ten_tour']); ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày khởi hành *</label>
                                <input type="date" name="ngay_khoi_hanh" class="form-control" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày kết thúc *</label>
                                <input type="date" name="ngay_ket_thuc" class="form-control" required
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Điểm tập trung</label>
                            <input type="text" name="diem_tap_trung" class="form-control" 
                                   placeholder="VD: Công viên 23/9, TP.HCM">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="trang_thai" class="form-select">
                                <option value="ChoXacNhan">Chờ xác nhận</option>
                                <option value="DaXacNhan" selected>Đã xác nhận</option>
                                <option value="SapKhoiHanh">Sắp khởi hành</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Thêm lịch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
        function filterTable() {
            const hdvFilter = document.getElementById('filterHDV').value;
            const trangThaiFilter = document.getElementById('filterTrangThai').value;
            const thangFilter = document.getElementById('filterThang').value;
            
            const rows = document.querySelectorAll('.schedule-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const hdv = row.dataset.hdv;
                const trangThai = row.dataset.trangthai;
                const thang = row.dataset.thang;
                
                const matchHDV = !hdvFilter || hdv === hdvFilter;
                const matchTrangThai = !trangThaiFilter || trangThai === trangThaiFilter;
                const matchThang = !thangFilter || thang === thangFilter;
                
                if (matchHDV && matchTrangThai && matchThang) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            console.log('Filtered:', visibleCount, 'rows visible');
        }

        function testAPI() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.textContent = 'Loading...';
            
            fetch('index.php?act=admin/hdv_get_schedule')
                .then(response => response.json())
                .then(data => {
                    resultDiv.textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    resultDiv.textContent = 'Error: ' + error.message;
                });
        }
    </script>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
