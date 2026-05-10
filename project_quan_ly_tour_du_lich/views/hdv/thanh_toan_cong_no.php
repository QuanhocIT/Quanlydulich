<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán công nợ - HDV</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$tours = $tours ?? [];
$congNoHDVs = $congNoHDVs ?? [];
$tongCongNo = 0;
$soHoaDonChoDuyet = 0;

foreach ($congNoHDVs as $row) {
    $tongCongNo += (float)($row['so_tien'] ?? 0);
    if (($row['trang_thai'] ?? '') !== 'DaDuyet') {
        $soHoaDonChoDuyet++;
    }
}
?>
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-wallet2"></i> Thanh toán công nợ tour</h3>
                <p class="mb-0 opacity-75">Gửi hóa đơn, theo dõi lịch sử xử lý và giữ toàn bộ hồ sơ công nợ của các tour bạn phụ trách trong một nơi thống nhất.</p>
            </div>
            <a href="index.php?act=hdv/dashboard" class="btn btn-light"><i class="bi bi-arrow-left"></i> Trang chủ</a>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tổng hóa đơn đã gửi</div>
                    <div class="hdv-kpi-value"><?php echo count($congNoHDVs); ?></div>
                    <div class="hdv-kpi-meta">Số lượt kê khai hiện có trong hệ thống</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tổng giá trị</div>
                    <div class="hdv-kpi-value" style="font-size:1.3rem"><?php echo number_format($tongCongNo, 0, ',', '.'); ?>đ</div>
                    <div class="hdv-kpi-meta">Cộng dồn toàn bộ công nợ đã kê khai</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="hdv-kpi-label">Chờ duyệt / xử lý</div>
                    <div class="hdv-kpi-value"><?php echo $soHoaDonChoDuyet; ?></div>
                    <div class="hdv-kpi-meta">Những hóa đơn chưa ở trạng thái hoàn tất</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Gửi hóa đơn mới</h4>
                    <p class="hdv-card-subtitle">Biểu mẫu gọn, rõ và đồng nhất với không gian làm việc HDV</p>
                </div>
                <span class="hdv-soft-badge primary"><i class="bi bi-send"></i> New claim</span>
            </div>
            <div class="hdv-card-body">
                <form method="POST" action="index.php?act=hdv/guiHoaDon" enctype="multipart/form-data" class="row g-3">
                    <div class="col-12">
                        <label for="tour_id" class="form-label">Chọn tour</label>
                        <select name="tour_id" id="tour_id" class="form-select" required>
                            <?php foreach ($tours as $tour): ?>
                                <option value="<?php echo (int)($tour['tour_id'] ?? 0); ?>">
                                    <?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tour'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="so_tien" class="form-label">Số tiền cần thanh toán</label>
                        <input type="number" name="so_tien" id="so_tien" class="form-control" min="0" step="1000" required>
                    </div>
                    <div class="col-md-6">
                        <label for="loai_cong_no" class="form-label">Loại hóa đơn</label>
                        <select name="loai_cong_no" id="loai_cong_no" class="form-select" required>
                            <option value="Thu">Hóa đơn thu</option>
                            <option value="Chi">Hóa đơn chi</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="anh_hoa_don" class="form-label">Ảnh hóa đơn</label>
                        <input type="file" name="anh_hoa_don" id="anh_hoa_don" class="form-control" accept="image/*,.pdf" required>
                    </div>
                    <div class="col-12">
                        <label for="ghi_chu" class="form-label">Ghi chú</label>
                        <textarea name="ghi_chu" id="ghi_chu" class="form-control" rows="5" placeholder="Ghi ngắn gọn nội dung thanh toán, bối cảnh tour hoặc thông tin cần kế toán lưu ý."></textarea>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Gửi hóa đơn</button>
                        <span class="hdv-pill"><i class="bi bi-info-circle"></i> Ưu tiên đính kèm ảnh rõ nét, đúng chứng từ</span>
                    </div>
                </form>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-body hdv-hero">
                <span class="hdv-hero-badge"><i class="bi bi-bank"></i> Tài chính minh bạch</span>
                <h1 class="hdv-hero-title">Công nợ được trình bày sạch, dễ rà soát để bạn gửi chứng từ nhanh mà vẫn giữ cảm giác chuyên nghiệp.</h1>
                <p class="hdv-hero-text">
                    Hãy đính kèm hóa đơn ngay sau khi phát sinh để đội kế toán và điều hành xử lý sớm.
                    Phần lịch sử bên dưới giúp đối chiếu số tiền, loại nghiệp vụ và trạng thái phê duyệt của từng hồ sơ.
                </p>
                <div class="hdv-pill-list">
                    <span class="hdv-pill"><i class="bi bi-image"></i> Chứng từ rõ nét</span>
                    <span class="hdv-pill"><i class="bi bi-check2-square"></i> Dễ đối chiếu theo tour</span>
                    <span class="hdv-pill"><i class="bi bi-clock-history"></i> Theo dõi trạng thái xử lý</span>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-section-spacer"></div>

    <div class="hdv-card">
        <div class="hdv-card-header">
            <div>
                <h4 class="hdv-card-title">Lịch sử hóa đơn đã gửi</h4>
                <p class="hdv-card-subtitle">Theo dõi từng hồ sơ công nợ theo tour, loại chứng từ và trạng thái hiện tại</p>
            </div>
            <span class="hdv-soft-badge neutral"><i class="bi bi-archive"></i> Archive</span>
        </div>
        <div class="hdv-card-body">
            <?php if (!empty($congNoHDVs)): ?>
                <div class="table-responsive">
                    <table class="table hdv-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Số tiền</th>
                                <th>Loại</th>
                                <th>Chứng từ</th>
                                <th>Trạng thái</th>
                                <th>Ngày gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($congNoHDVs as $hd): ?>
                                <?php
                                $status = $hd['trang_thai'] ?? 'ChoDuyet';
                                $badgeClass = $status === 'DaDuyet' ? 'primary' : ($status === 'TuChoi' ? 'danger' : 'warning');
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($hd['ten_tour'] ?? ('Tour #' . ($hd['tour_id'] ?? 'N/A'))); ?></strong>
                                        <div class="hdv-muted small">Mã tour: <?php echo htmlspecialchars((string)($hd['tour_id'] ?? 'N/A')); ?></div>
                                    </td>
                                    <td><strong><?php echo number_format((float)($hd['so_tien'] ?? 0), 0, ',', '.'); ?>đ</strong></td>
                                    <td><span class="hdv-soft-badge neutral"><?php echo htmlspecialchars($hd['loai_cong_no'] ?? 'N/A'); ?></span></td>
                                    <td>
                                        <?php if (!empty($hd['anh_hoa_don'])): ?>
                                            <a href="<?php echo htmlspecialchars($hd['anh_hoa_don']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-box-arrow-up-right"></i> Xem file
                                            </a>
                                        <?php else: ?>
                                            <span class="hdv-muted">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="hdv-soft-badge <?php echo $badgeClass; ?>">
                                            <i class="bi bi-circle-fill"></i> <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo !empty($hd['ngay_gui']) ? date('d/m/Y H:i', strtotime($hd['ngay_gui'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="hdv-empty">
                    <i class="bi bi-receipt-cutoff"></i>
                    Bạn chưa gửi hóa đơn công nợ nào.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
