<?php 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php?act=auth/login');
    exit;
}
$pageTitle = 'Thêm Khách';
$currentPage = 'lichKhoiHanh';
ob_start();
?>
<style>
    .form-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }
    .form-control, .form-select {
        background: rgba(30, 30, 30, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 10px 15px;
        border-radius: 4px;
    }
    .form-control:focus, .form-select:focus {
        background: rgba(30, 30, 30, 0.9);
        border-color: var(--accent-gold);
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
        color: var(--text-light);
    }
    .form-label {
        color: var(--text-light);
        margin-bottom: 8px;
        display: block;
    }
    .btn {
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }
    .btn-primary {
        background: var(--accent-gold);
        color: #000;
    }
    .btn-primary:hover {
        background: #ffd700;
    }
    .btn-secondary {
        background: rgba(108, 117, 125, 0.3);
        color: var(--text-light);
        border: 1px solid rgba(108, 117, 125, 0.5);
    }
    .btn-secondary:hover {
        background: rgba(108, 117, 125, 0.5);
    }
    .card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        backdrop-filter: blur(10px);
    }
    .card-header {
        background: rgba(45, 45, 45, 0.7);
        color: var(--text-light);
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .card-body {
        padding: 20px;
        color: var(--text-light);
    }
    .alert-danger {
        background: rgba(220, 53, 69, 0.2);
        border: 1px solid rgba(220, 53, 69, 0.5);
        color: #dc3545;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .text-danger { color: #dc3545 !important; }
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }
    .row > * {
        padding-left: 15px;
        padding-right: 15px;
    }
    .col-md-4, .col-md-6 { width: 50%; }
    .col-12 { width: 100%; }
    .g-3 { gap: 1rem; }
    .mb-3 { margin-bottom: 1rem; }
    .mt-4 { margin-top: 1.5rem; }
    @media (max-width: 768px) {
        .col-md-4, .col-md-6 { width: 100%; }
    }
</style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-plus-circle" style="color: var(--accent-gold);"></i> Thêm Khách Vào Lịch Khởi Hành
        </h1>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 style="margin: 0; color: var(--text-light);"><i class="bi bi-plus-circle"></i> Thêm Khách Vào Lịch Khởi Hành</h5>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Tour:</strong> <?php echo htmlspecialchars($tour['ten_tour'] ?? 'N/A'); ?><br>
                    <strong>Ngày khởi hành:</strong> <?php echo $lichKhoiHanh['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($lichKhoiHanh['ngay_khoi_hanh'])) : 'N/A'; ?>
                </div>

                <form method="POST" action="index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" name="ho_ten" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="dia_chi" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Số người <span class="text-danger">*</span></label>
                            <input type="number" name="so_nguoi" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tổng tiền</label>
                            <input type="number" name="tong_tien" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Trạng thái</label>
                            <select name="trang_thai" class="form-select">
                                <option value="ChoXacNhan">Chờ xác nhận</option>
                                <option value="DaCoc">Đã cọc</option>
                                <option value="HoanTat">Hoàn tất</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="ghi_chu" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Thêm khách</button>
                        <a href="index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>

