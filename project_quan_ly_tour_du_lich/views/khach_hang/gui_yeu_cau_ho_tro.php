<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu hỗ trợ - Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .support-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-question-circle me-2"></i>Yêu cầu hỗ trợ</h2>
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="support-card">
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Vui lòng mô tả chi tiết yêu cầu hỗ trợ của bạn. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.
            </div>

            <form method="POST" action="index.php?act=khachHang/guiYeuCauHoTro">
                <div class="mb-3">
                    <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="tieu_de" value="Yêu cầu hỗ trợ" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mức độ ưu tiên</label>
                    <select class="form-select" name="muc_do_uu_tien">
                        <option value="TrungBinh" selected>Trung bình</option>
                        <option value="Thap">Thấp</option>
                        <option value="Cao">Cao</option>
                        <option value="KhanCap">Khẩn cấp</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nội dung yêu cầu <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="noi_dung" rows="8" placeholder="Mô tả chi tiết yêu cầu hỗ trợ của bạn..." required></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-send me-2"></i>Gửi yêu cầu hỗ trợ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


