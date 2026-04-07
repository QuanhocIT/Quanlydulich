<?php
$pageTitle = 'Ghi nhận chi phí thực tế';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
        .form-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 32px;
            backdrop-filter: blur(10px);
        }
        .form-section {
            margin-bottom: 24px;
        }
        .form-label {
            font-weight: 600;
            color: var(--text-light);
            display: block;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(30, 30, 30, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            border-radius: 4px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(30, 30, 30, 0.9);
            border-color: var(--accent-gold);
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
            color: var(--text-light);
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: var(--accent-gold);
            color: #000;
        }
        .btn-primary:hover {
            background: #ffd700;
        }
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        .px-4 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
    </style>

<div style="padding: 20px; max-width: 700px; margin: 0 auto;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="fa fa-money-bill-wave" style="color: var(--accent-gold);"></i> Ghi nhận chi phí thực tế
        </h1>
    </div>
    
    <div class="form-card">
        <form method="post" enctype="multipart/form-data">
            <div class="form-section">
                <label class="form-label">Tour</label>
                <select name="tour_id" class="form-select" required>
                    <?php foreach ($tours as $tour): ?>
                        <option value="<?= $tour['tour_id'] ?>" <?= isset($duToan) && $duToan['tour_id'] == $tour['tour_id'] ? 'selected' : '' ?>><?= htmlspecialchars($tour['ten_tour']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-section">
                <label class="form-label">Dự toán liên quan</label>
                <select name="du_toan_id" class="form-select" required>
                    <?php foreach ($duToans as $dt): ?>
                        <option value="<?= $dt['du_toan_id'] ?>" <?= isset($duToan) && $duToan['du_toan_id'] == $dt['du_toan_id'] ? 'selected' : '' ?>><?= htmlspecialchars($dt['ten_tour']) ?> (<?= number_format($dt['tong_du_toan']) ?>đ)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-section">
                <label class="form-label">Loại chi phí</label>
                <select name="loai_chi_phi" class="form-select" required>
                    <option value="PhuongTien">Phương tiện</option>
                    <option value="LuuTru">Lưu trú</option>
                    <option value="VeThamQuan">Vé tham quan</option>
                    <option value="AnUong">Ăn uống</option>
                    <option value="HuongDanVien">Hướng dẫn viên</option>
                    <option value="DichVuBoSung">Dịch vụ bổ sung</option>
                    <option value="PhatSinh">Phát sinh dự kiến</option>
                </select>
            </div>
            <div class="form-section">
                <label class="form-label">Tên khoản chi</label>
                <input type="text" name="ten_khoan_chi" class="form-control" required>
            </div>
            <div class="form-section">
                <label class="form-label">Số tiền (VNĐ)</label>
                <input type="number" name="so_tien" class="form-control" required min="0">
            </div>
            <div class="form-section">
                <label class="form-label">Ngày phát sinh</label>
                <input type="date" name="ngay_phat_sinh" class="form-control" required>
            </div>
            <div class="form-section">
                <label class="form-label">Mô tả</label>
                <textarea name="mo_ta" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-section">
                <label class="form-label">Chứng từ (hóa đơn, ảnh...)</label>
                <input type="file" name="chung_tu" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary px-4 py-2"><i class="fa fa-save"></i> Lưu chi phí</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
