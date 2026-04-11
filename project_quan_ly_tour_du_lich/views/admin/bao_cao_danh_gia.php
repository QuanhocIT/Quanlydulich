<?php
$pageTitle = 'Báo cáo Đánh giá Tổng hợp';
$currentPage = 'danhGia';
ob_start();
?>
<style>
        .report-section {
            margin-bottom: 30px;
        }
        .top-item {
            background: rgba(102, 126, 234, 0.3);
            border: 1px solid rgba(102, 126, 234, 0.5);
            color: var(--text-light);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: transform 0.2s;
            backdrop-filter: blur(10px);
        }
        .top-item:hover {
            transform: translateX(5px);
            background: rgba(102, 126, 234, 0.4);
        }
        .warning-item {
            background: rgba(245, 87, 108, 0.3);
            border: 1px solid rgba(245, 87, 108, 0.5);
            color: var(--text-light);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            backdrop-filter: blur(10px);
        }
        .rating-badge {
            font-size: 1.3em;
            padding: 8px 15px;
        }
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: rgba(45, 45, 45, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .card-body {
            color: var(--text-light);
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-success {
            background: rgba(40, 167, 69, 0.3) !important;
            color: #5cb85c !important;
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
        .bg-primary {
            background: rgba(13, 110, 253, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-dark {
            background: rgba(33, 37, 41, 0.3) !important;
            color: #adb5bd !important;
        }
        .bg-light {
            background: rgba(248, 249, 250, 0.1) !important;
            color: var(--text-light) !important;
        }
        .text-white {
            color: var(--text-light) !important;
        }
        .text-success {
            color: #5cb85c !important;
        }
        .text-danger {
            color: #dc3545 !important;
        }
        .text-secondary {
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
        .btn-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(108, 117, 125, 0.5);
        }
        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.5);
        }
        .btn-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-success:hover {
            background: rgba(40, 167, 69, 0.5);
        }
        .table {
            color: var(--text-light);
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background: rgba(255, 255, 255, 0.03);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>

<div style="padding: 20px;">
    <?php
    $exportParams = ['act' => 'admin/danhGia/export'];
    if (!empty($_GET['loai'])) {
        $exportParams['loai'] = $_GET['loai'];
    }
    if (!empty($_GET['id'])) {
        $exportParams['id'] = $_GET['id'];
    }
    $excelUrl = 'index.php?' . http_build_query($exportParams + ['format' => 'excel']);
    $pdfUrl = 'index.php?' . http_build_query($exportParams + ['format' => 'pdf']);
    ?>
    <div class="page-header-section" style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-file-earmark-bar-graph" style="color: var(--accent-gold);"></i> Báo cáo Đánh giá Tổng hợp
            </h1>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
                <a href="index.php?act=admin/danhGia" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> In báo cáo
                </button>
                <a href="<?= htmlspecialchars($excelUrl) ?>" class="btn btn-success">
                    <i class="bi bi-file-excel"></i> Xuất Excel
                </a>
                <a href="<?= htmlspecialchars($pdfUrl) ?>" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
                </a>
            </div>
        </div>
    </div>
                
                <!-- TOP TOUR TỐT NHẤT -->
                <div class="report-section">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Tour Được Đánh Giá Cao Nhất</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($report['top_tour'])): ?>
                                <p class="text-muted">Chưa có dữ liệu</p>
                            <?php else: ?>
                                <?php foreach ($report['top_tour'] as $index => $tour): ?>
                                    <div class="top-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">
                                                    #<?= $index + 1 ?>. 
                                                    <a href="index.php?act=admin/danhGia/baoCao&loai=tour&id=<?= $tour['tour_id'] ?>" 
                                                       style="color: var(--text-light); text-decoration: none;">
                                                        <?= htmlspecialchars($tour['ten_tour']) ?>
                                                    </a>
                                                </h5>
                                                <small>
                                                    <i class="bi bi-chat-left-text"></i> 
                                                    <?= $tour['so_danh_gia'] ?> đánh giá
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="rating-badge badge bg-warning text-dark">
                                                    <?= number_format($tour['diem_tb'], 1) ?> <i class="bi bi-star-fill"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- TOUR CẦN CẢI THIỆN -->
                <div class="report-section">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Tour Cần Cải Thiện</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($report['tour_can_cai_thien'])): ?>
                                <p class="text-success"><i class="bi bi-check-circle"></i> Tất cả tour đều đạt chất lượng tốt!</p>
                            <?php else: ?>
                                <?php foreach ($report['tour_can_cai_thien'] as $tour): ?>
                                    <div class="warning-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">
                                                    <a href="index.php?act=admin/danhGia/baoCao&loai=tour&id=<?= $tour['tour_id'] ?>" 
                                                       style="color: var(--text-light); text-decoration: none;">
                                                        <?= htmlspecialchars($tour['ten_tour']) ?>
                                                    </a>
                                                </h5>
                                                <small>
                                                    <i class="bi bi-chat-left-text"></i> 
                                                    <?= $tour['so_danh_gia'] ?> đánh giá
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="rating-badge badge bg-dark">
                                                    <?= number_format($tour['diem_tb'], 1) ?> <i class="bi bi-star-fill"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- TOP NHÀ CUNG CẤP -->
                <div class="report-section">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0"><i class="bi bi-building"></i> Top 10 Nhà Cung Cấp Tốt Nhất</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($report['top_nha_cung_cap'])): ?>
                                <p class="text-muted">Chưa có dữ liệu</p>
                            <?php else: ?>
                                <?php foreach ($report['top_nha_cung_cap'] as $index => $ncc): ?>
                                    <div class="top-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">
                                                    #<?= $index + 1 ?>. 
                                                    <a href="index.php?act=admin/danhGia/baoCao&loai=ncc&id=<?= $ncc['nha_cung_cap_id'] ?>" 
                                                       style="color: var(--text-light); text-decoration: none;">
                                                        <?= htmlspecialchars($ncc['ten_nha_cung_cap']) ?>
                                                    </a>
                                                </h5>
                                                <small>
                                                    <i class="bi bi-chat-left-text"></i> 
                                                    <?= $ncc['so_danh_gia'] ?> đánh giá
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="rating-badge badge bg-warning text-dark">
                                                    <?= number_format($ncc['diem_tb'], 1) ?> <i class="bi bi-star-fill"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- NHÀ CUNG CẤP CẦN CẢI THIỆN -->
                <?php if (!empty($report['ncc_can_cai_thien'])): ?>
                <div class="report-section">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h4 class="mb-0"><i class="bi bi-exclamation-circle"></i> Nhà Cung Cấp Cần Xem Xét/Thay Thế</h4>
                        </div>
                        <div class="card-body">
                            <?php foreach ($report['ncc_can_cai_thien'] as $ncc): ?>
                                <div class="warning-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="index.php?act=admin/danhGia/baoCao&loai=ncc&id=<?= $ncc['nha_cung_cap_id'] ?>" 
                                                   style="color: var(--text-light); text-decoration: none;">
                                                    <?= htmlspecialchars($ncc['ten_nha_cung_cap']) ?>
                                                </a>
                                            </h5>
                                            <small>
                                                <i class="bi bi-chat-left-text"></i> 
                                                <?= $ncc['so_danh_gia'] ?> đánh giá
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-exclamation-triangle"></i> 
                                                    Đề xuất: Yêu cầu cải thiện hoặc tìm nhà cung cấp thay thế
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="rating-badge badge bg-dark">
                                                <?= number_format($ncc['diem_tb'], 1) ?> <i class="bi bi-star-fill"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- TOP NHÂN SỰ -->
                <div class="report-section">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h4 class="mb-0"><i class="bi bi-people"></i> Top 10 Nhân Sự Xuất Sắc - Đề Xuất Khen Thưởng</h4>
                        </div>
                        <div class="card-body">
                            <?php if (empty($report['top_nhan_su'])): ?>
                                <p class="text-muted">Chưa có dữ liệu</p>
                            <?php else: ?>
                                <?php foreach ($report['top_nhan_su'] as $index => $ns): ?>
                                    <div class="top-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-1">
                                                    #<?= $index + 1 ?>. <?= htmlspecialchars($ns['ho_ten']) ?>
                                                </h5>
                                                <small>
                                                    <i class="bi bi-chat-left-text"></i> 
                                                    <?= $ns['so_danh_gia'] ?> đánh giá
                                                </small>
                                                <?php if ($index < 3): ?>
                                                    <div class="mt-2">
                                                        <span class="badge bg-light text-dark">
                                                            <i class="bi bi-award"></i> Đề xuất khen thưởng
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="rating-badge badge bg-warning text-dark">
                                                    <?= number_format($ns['diem_tb'], 1) ?> <i class="bi bi-star-fill"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- NHÂN SỰ CẦN NHẮC NHỞ -->
                <?php if (!empty($report['nhan_su_can_nhac_nho'])): ?>
                <div class="report-section">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h4 class="mb-0"><i class="bi bi-bell"></i> Nhân Sự Cần Nhắc Nhở/Đào Tạo Lại</h4>
                        </div>
                        <div class="card-body">
                            <?php foreach ($report['nhan_su_can_nhac_nho'] as $ns): ?>
                                <div class="warning-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1"><?= htmlspecialchars($ns['ho_ten']) ?></h5>
                                            <small>
                                                <i class="bi bi-chat-left-text"></i> 
                                                <?= $ns['so_danh_gia'] ?> đánh giá
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge bg-light text-dark">
                                                    <i class="bi bi-exclamation-triangle"></i> 
                                                    Đề xuất: Nhắc nhở, đào tạo lại hoặc xem xét điều chuyển
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="rating-badge badge bg-dark">
                                                <?= number_format($ns['diem_tb'], 1) ?> <i class="bi bi-star-fill"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- THỐNG KÊ THEO THÁNG -->
                <?php if (!empty($report['theo_thang'])): ?>
                <div class="report-section">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="bi bi-graph-up"></i> Xu Hướng 6 Tháng Gần Đây</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tháng</th>
                                            <th>Số lượng đánh giá</th>
                                            <th>Điểm trung bình</th>
                                            <th>Xu hướng</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $prev_diem = null;
                                        foreach ($report['theo_thang'] as $thang): 
                                        ?>
                                            <tr>
                                                <td><?= date('m/Y', strtotime($thang['thang'] . '-01')) ?></td>
                                                <td><?= $thang['so_luong'] ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $thang['diem_tb'] >= 4 ? 'success' : ($thang['diem_tb'] <= 2 ? 'danger' : 'warning') ?>">
                                                        <?= number_format($thang['diem_tb'], 1) ?> <i class="bi bi-star-fill"></i>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($prev_diem !== null): ?>
                                                        <?php if ($thang['diem_tb'] > $prev_diem): ?>
                                                            <i class="bi bi-arrow-up-circle-fill text-success"></i> Tăng
                                                        <?php elseif ($thang['diem_tb'] < $prev_diem): ?>
                                                            <i class="bi bi-arrow-down-circle-fill text-danger"></i> Giảm
                                                        <?php else: ?>
                                                            <i class="bi bi-dash-circle text-secondary"></i> Không đổi
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php 
                                            $prev_diem = $thang['diem_tb'];
                                        endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
