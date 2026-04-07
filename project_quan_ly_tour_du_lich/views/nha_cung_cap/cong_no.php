<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công nợ - Nhà cung cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/supplier.css">
</head>
<body class="supplier-body">
    <div class="container-fluid supplier-shell">
        <section class="supplier-page-header">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="supplier-eyebrow"><i class="bi bi-cash-stack"></i> Công nợ</span>
                    <h1 class="supplier-page-title">Theo dõi công nợ</h1>
                    <p class="supplier-page-subtitle">Tổng hợp giá trị dịch vụ đã xác nhận và danh sách đối soát theo một bố cục rõ ràng, nhẹ mắt và đồng nhất với toàn bộ khu vực nhà cung cấp.</p>
                </div>
                <div class="col-lg-4">
                    <div class="supplier-header-actions">
                        <a href="index.php?act=nhaCungCap/hopDong" class="btn btn-outline-secondary">
                            <i class="bi bi-clock-history"></i> Xem lịch sử hợp tác
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php
            $currentTab = 'congNo';
            include __DIR__ . '/partials/main_nav.php';
        ?>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="supplier-stat-card primary h-100">
                    <div class="supplier-stat-label">Tổng công nợ</div>
                    <p class="supplier-stat-value"><?php echo number_format($congNo['tong_cong_no'] ?? 0, 0, ',', '.'); ?>đ</p>
                    <div class="supplier-stat-meta">Giá trị từ các dịch vụ đã được xác nhận.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card supplier-surface-card supplier-metric-card">
                    <div class="card-body">
                        <div class="supplier-metric-label">Thống kê đối soát</div>
                        <div class="supplier-metric-value"><?php echo $congNo['so_dich_vu'] ?? 0; ?></div>
                        <p class="mb-1"><strong>Số dịch vụ đã xác nhận:</strong> <?php echo $congNo['so_dich_vu'] ?? 0; ?></p>
                        <p class="mb-0"><strong>Tổng số dòng dữ liệu:</strong> <?php echo count($dichVuDaXacNhan); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card supplier-section-card">
            <div class="card-header">
                <h5 class="supplier-card-title"><i class="bi bi-list-ul"></i> Danh sách công nợ</h5>
                <div class="supplier-card-subtitle">Chi tiết các dịch vụ đã xác nhận và giá trị tương ứng để tiện đối chiếu.</div>
            </div>
            <div class="card-body">
                <?php if (empty($dichVuDaXacNhan)): ?>
                    <div class="supplier-empty-state">
                        <div class="supplier-empty-icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Hiện chưa có công nợ nào cần theo dõi.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table supplier-table">
                            <thead>
                                <tr>
                                    <th>Tour</th>
                                    <th>Dịch vụ</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Thời gian xác nhận</th>
                                    <th class="text-end">Số tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $tongTien = 0;
                                foreach ($dichVuDaXacNhan as $dv):
                                    $tongTien += $dv['gia_tien'] ?? 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dv['ten_tour'] ?? 'N/A'); ?></strong></td>
                                    <td>
                                        <span class="supplier-badge-soft info"><?php echo htmlspecialchars($dv['loai_dich_vu']); ?></span>
                                        <br><small><?php echo htmlspecialchars($dv['ten_dich_vu']); ?></small>
                                    </td>
                                    <td><?php echo $dv['ngay_bat_dau'] ? date('d/m/Y', strtotime($dv['ngay_bat_dau'])) : '<span class="text-muted">-</span>'; ?></td>
                                    <td><?php echo $dv['ngay_ket_thuc'] ? date('d/m/Y', strtotime($dv['ngay_ket_thuc'])) : '<span class="text-muted">-</span>'; ?></td>
                                    <td><?php echo $dv['thoi_gian_xac_nhan'] ? date('d/m/Y H:i', strtotime($dv['thoi_gian_xac_nhan'])) : '<span class="text-muted">-</span>'; ?></td>
                                    <td class="text-end"><strong class="text-success"><?php echo number_format($dv['gia_tien'] ?? 0, 0, ',', '.'); ?>đ</strong></td>
                                    <td><span class="supplier-badge-soft success">Đã xác nhận</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Tổng cộng:</th>
                                    <th class="text-end"><strong><?php echo number_format($tongTien, 0, ',', '.'); ?>đ</strong></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
