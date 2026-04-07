<?php
$pageTitle = 'Tạo Lịch Khởi Hành';
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
    table {
        width: 100%;
        border-collapse: collapse;
        color: var(--text-light);
    }
    table th {
        background: rgba(45, 45, 45, 0.7);
        color: var(--text-light);
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    table input, table select, table textarea {
        width: 100%;
        padding: 10px;
        background: rgba(30, 30, 30, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        border-radius: 4px;
    }
    table input:focus, table select:focus, table textarea:focus {
        background: rgba(30, 30, 30, 0.9);
        border-color: var(--accent-gold);
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
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
</style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-plus-circle" style="color: var(--accent-gold);"></i> Tạo Lịch Khởi Hành cho Tour: <?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?>
        </h1>
        <a href="index.php?act=admin/chiTietTour&id=<?php echo $tour['tour_id']; ?>" style="color: var(--accent-gold); text-decoration: none;">← Quay lại chi tiết tour</a>
    </div>

    <div class="form-section">
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.5); color: #dc3545; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

            <form method="POST" action="index.php?act=tour/taoLichKhoiHanh&tour_id=<?php echo $tour['tour_id']; ?>">
                <input type="hidden" name="tour_id" value="<?php echo $tour['tour_id']; ?>">
                <table>
                    <tr>
                        <th>Ngày khởi hành</th>
                        <td><input type="date" name="ngay_khoi_hanh" required></td>
                    </tr>
                    <tr>
                        <th>Giờ xuất phát</th>
                        <td><input type="time" name="gio_xuat_phat"></td>
                    </tr>
                    <tr>
                        <th>Ngày kết thúc</th>
                        <td><input type="date" name="ngay_ket_thuc"></td>
                    </tr>
                    <tr>
                        <th>Giờ kết thúc</th>
                        <td><input type="time" name="gio_ket_thuc"></td>
                    </tr>
                    <tr>
                        <th>Điểm tập trung</th>
                        <td><input type="text" name="diem_tap_trung" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <th>Số chỗ</th>
                        <td><input type="number" name="so_cho" value="50" min="1" required></td>
                    </tr>
                    <tr>
                        <th>HDV chính</th>
                        <td>
                            <select name="hdv_id">
                                <option value="">-- Chọn HDV --</option>
                                <?php foreach ($hdvList as $hdv): ?>
                                    <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                        <?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Trạng thái</th>
                        <td>
                            <select name="trang_thai">
                                <option value="SapKhoiHanh">Sắp khởi hành</option>
                                <option value="DangChay">Đang chạy</option>
                                <option value="HoanThanh">Hoàn thành</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Ghi chú</th>
                        <td><textarea name="ghi_chu" rows="3" style="width: 100%;"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Tạo lịch khởi hành</button>
                        </td>
                    </tr>
                </table>
            </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>

