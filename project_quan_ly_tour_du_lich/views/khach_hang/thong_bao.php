<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .notification-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .notification-card:hover {
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
        }
        .notification-card.unread {
            background: #f8f9ff;
            border-left-color: #667eea;
        }
        .notification-card.read {
            background: #f8f9fa;
            border-left-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-bell me-2"></i>Thông báo</h2>
            <a href="index.php?act=khachHang/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Bạn có <span class="text-primary"><?php echo $thongBaoChuaDoc; ?></span> thông báo chưa đọc</h5>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($thongBaoList)): ?>
            <?php foreach ($thongBaoList as $tb): ?>
                <div class="notification-card <?php echo empty($tb['da_doc']) || $tb['da_doc'] == 0 ? 'unread' : 'read'; ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h5 class="mb-0 me-2"><?php echo htmlspecialchars($tb['tieu_de'] ?? ''); ?></h5>
                                <?php if (empty($tb['da_doc']) || $tb['da_doc'] == 0): ?>
                                    <span class="badge bg-primary">Mới</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted mb-2"><?php echo nl2br(htmlspecialchars($tb['noi_dung'] ?? '')); ?></p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                <?php 
                                if (!empty($tb['thoi_gian_gui'])) {
                                    echo date('d/m/Y H:i', strtotime($tb['thoi_gian_gui']));
                                } elseif (!empty($tb['created_at'])) {
                                    echo date('d/m/Y H:i', strtotime($tb['created_at']));
                                }
                                ?>
                            </small>
                        </div>
                        <?php if (empty($tb['da_doc']) || $tb['da_doc'] == 0): ?>
                            <a href="index.php?act=khachHang/thongBao&mark_read=<?php echo $tb['id']; ?>" class="btn btn-sm btn-outline-primary ms-2">
                                <i class="bi bi-check"></i> Đánh dấu đã đọc
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Bạn chưa có thông báo nào.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


