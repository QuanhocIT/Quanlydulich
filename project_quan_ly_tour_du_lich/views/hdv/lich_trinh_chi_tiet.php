<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch trình chi tiết Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .info-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="bi bi-calendar2-week"></i> Lịch trình chi tiết Tour
                    </h3>
                    <p class="mb-0 opacity-75">Xem toàn bộ lịch trình tour</p>
                </div>
                <a href="javascript:history.back()" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
    <div class="container">
        <?php
        require_once __DIR__ . '/../../models/Tour.php';
        $tour_id = $_GET['tour_id'] ?? 0;
        $tour = (new Tour())->findById($tour_id);
        $lichTrinhList = (new Tour())->getLichTrinhByTourId($tour_id);
        ?>
        <div class="info-card mb-4">
            <h5 class="mb-3"><i class="bi bi-info-circle"></i> Thông tin Tour</h5>
            <div><strong>Tên tour:</strong> <?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?></div>
            <div><strong>Ngày khởi hành:</strong> <?php echo !empty($tour['ngay_khoi_hanh']) ? date('d/m/Y H:i', strtotime($tour['ngay_khoi_hanh'])) : ''; ?></div>
            <div><strong>Ngày kết thúc:</strong> <?php echo !empty($tour['ngay_ket_thuc']) ? date('d/m/Y H:i', strtotime($tour['ngay_ket_thuc'])) : ''; ?></div>
            <div><strong>Điểm tập trung:</strong> <?php echo htmlspecialchars($tour['diem_tap_trung'] ?? ''); ?></div>
        </div>
        <div class="info-card">
            <h5 class="mb-3"><i class="bi bi-list-check"></i> Lịch trình chi tiết</h5>
            <?php if (!empty($lichTrinhList)): ?>
                <ol class="list-group list-group-numbered">
                    <?php foreach ($lichTrinhList as $lichTrinh): ?>
                        <li class="list-group-item mb-2">
                            <div class="fw-bold text-primary">Ngày <?= htmlspecialchars($lichTrinh['ngay_thu']) ?>: <?= htmlspecialchars($lichTrinh['dia_diem']) ?></div>
                            <div><?= nl2br(htmlspecialchars($lichTrinh['hoat_dong'])) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <div class="text-muted">Chưa có lịch trình chi tiết cho tour này.</div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
