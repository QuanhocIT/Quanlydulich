<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử hợp tác - Nhà cung cấp</title>
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
                    <span class="supplier-eyebrow"><i class="bi bi-clock-history"></i> Hợp tác</span>
                    <h1 class="supplier-page-title">Lịch sử hợp tác</h1>
                    <p class="supplier-page-subtitle">Rà soát toàn bộ các lần phối hợp theo tour, trạng thái xử lý và giá trị dịch vụ trong một giao diện thống nhất.</p>
                </div>
                <div class="col-lg-4">
                    <div class="supplier-header-actions">
                        <a href="index.php?act=nhaCungCap/congNo" class="btn btn-outline-secondary">
                            <i class="bi bi-cash-stack"></i> Mở công nợ
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars((string)($_SESSION['success'] ?? ''), ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars((string)($_SESSION['error'] ?? ''), ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php
            $currentTab = 'hopDong';
            include __DIR__ . '/partials/main_nav.php';
        ?>

        <div class="card supplier-section-card">
            <div class="card-header">
                <h5 class="supplier-card-title"><i class="bi bi-clock-history"></i> Nhật ký hợp tác</h5>
                <div class="supplier-card-subtitle">Danh sách lịch sử giúp bạn nhìn nhanh tiến độ và kết quả của từng hạng mục.</div>
            </div>
            <div class="card-body">
                <?php if (empty($lichSu)): ?>
                    <div class="supplier-empty-state">
                        <div class="supplier-empty-icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Chưa có lịch sử hợp tác.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table supplier-table">
                            <thead>
                                <tr>
                                    <th>Tour</th>
                                    <th>Loại dịch vụ</th>
                                    <th>Tên dịch vụ</th>
                                    <th>Số lượng</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Giá tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $statusMap = [
                                    'ChoXacNhan' => ['text' => 'Chờ xác nhận', 'class' => 'warning'],
                                    'DaXacNhan' => ['text' => 'Đã xác nhận', 'class' => 'success'],
                                    'TuChoi' => ['text' => 'Từ chối', 'class' => 'danger'],
                                    'Huy' => ['text' => 'Hủy', 'class' => 'secondary'],
                                    'HoanTat' => ['text' => 'Hoàn tất', 'class' => 'info']
                                ];

                                $loaiDichVuMap = [
                                    'Xe' => 'Xe',
                                    'KhachSan' => 'Khách sạn',
                                    'VeMayBay' => 'Vé máy bay',
                                    'NhaHang' => 'Nhà hàng',
                                    'DiemThamQuan' => 'Điểm tham quan',
                                    'Visa' => 'Visa',
                                    'BaoHiem' => 'Bảo hiểm',
                                    'Khac' => 'Khác'
                                ];

                                foreach ($lichSu as $ls):
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($ls['ten_tour'] ?? 'N/A'); ?></strong>
                                        <?php if ($ls['so_booking']): ?>
                                            <br><small class="text-muted"><i class="bi bi-people"></i> <?php echo $ls['so_booking']; ?> booking</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="supplier-badge-soft info"><?php echo $loaiDichVuMap[$ls['loai_dich_vu']] ?? $ls['loai_dich_vu']; ?></span></td>
                                    <td><?php echo htmlspecialchars($ls['ten_dich_vu']); ?></td>
                                    <td><?php echo $ls['so_luong']; ?> <?php if ($ls['don_vi']): ?><small class="text-muted"><?php echo $ls['don_vi']; ?></small><?php endif; ?></td>
                                    <td><?php echo $ls['ngay_bat_dau'] ? date('d/m/Y', strtotime($ls['ngay_bat_dau'])) : '<span class="text-muted">-</span>'; ?></td>
                                    <td><?php echo $ls['ngay_ket_thuc'] ? date('d/m/Y', strtotime($ls['ngay_ket_thuc'])) : '<span class="text-muted">-</span>'; ?></td>
                                    <td>
                                        <?php if ($ls['gia_tien']): ?>
                                            <strong class="text-success"><?php echo number_format($ls['gia_tien'], 0, ',', '.'); ?>đ</strong>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có giá</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $status = $statusMap[$ls['trang_thai']] ?? ['text' => $ls['trang_thai'], 'class' => 'secondary']; ?>
                                        <span class="supplier-badge-soft <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span>
                                    </td>
                                    <td><?php echo $ls['created_at'] ? date('d/m/Y H:i', strtotime($ls['created_at'])) : '<span class="text-muted">-</span>'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
