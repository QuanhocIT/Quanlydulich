<?php 
if (!hasRole('Admin')) {
    header('Location: index.php?act=auth/login');
    exit;
}

// $chiTiet chứa 1 bản ghi lịch sử xóa
$thongTin = [];
if (!empty($chiTiet['thong_tin_nha_cung_cap'])) {
    $decoded = json_decode($chiTiet['thong_tin_nha_cung_cap'], true);
    if (is_array($decoded)) {
        $thongTin = $decoded;
    }
}

$loaiDichVuMap = [
    'KhachSan' => 'Khách sạn',
    'NhaHang'  => 'Nhà hàng',
    'Xe'       => 'Xe vận chuyển',
    'Ve'       => 'Vé máy bay / tàu',
    'Visa'     => 'Visa',
    'BaoHiem'  => 'Bảo hiểm',
    'Khac'     => 'Khác'
];
?>
<?php 
if (!hasRole('Admin')) {
    header('Location: index.php?act=auth/login');
    exit;
}
$pageTitle = 'Chi tiết nhật ký xóa nhà cung cấp';
$currentPage = 'nhaCungCap';
ob_start();
?>
<style>
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
        .bg-light {
            background: rgba(248, 249, 250, 0.1) !important;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-secondary {
            background: rgba(108, 117, 125, 0.3) !important;
            color: #adb5bd !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .text-danger {
            color: #dc3545 !important;
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
        .btn-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(108, 117, 125, 0.5);
        }
        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.5);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
    </style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-file-earmark-text" style="color: #dc3545;"></i> Chi tiết nhật ký xóa nhà cung cấp
            </h1>
            <a href="index.php?act=admin/lichSuXoaNhaCungCap" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <?php if (empty($chiTiet)): ?>
        <div class="alert alert-danger">
            Không tìm thấy bản ghi lịch sử xóa.
        </div>
    <?php else: ?>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Thông tin chung</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>ID nhật ký:</strong> <?php echo htmlspecialchars($chiTiet['id'] ?? ''); ?></p>
                        <p><strong>Nhà cung cấp ID:</strong> 
                            <span class="badge bg-secondary">
                                #<?php echo htmlspecialchars($chiTiet['nha_cung_cap_id'] ?? 'N/A'); ?>
                            </span>
                        </p>
                        <p><strong>Thời gian xóa:</strong> 
                            <?php 
                            echo !empty($chiTiet['thoi_gian_xoa']) 
                                ? date('d/m/Y H:i:s', strtotime($chiTiet['thoi_gian_xoa'])) 
                                : 'N/A'; 
                            ?>
                        </p>
                        <p><strong>Lý do xóa:</strong><br>
                            <?php if (!empty($chiTiet['ly_do_xoa'])): ?>
                                <span class="text-danger"><?php echo nl2br(htmlspecialchars($chiTiet['ly_do_xoa'])); ?></span>
                            <?php else: ?>
                                <span class="text-muted">Không có</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Người thực hiện xóa</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($chiTiet['nguoi_xoa'] ?? 'N/A'); ?></p>
                        <p><strong>Email:</strong> 
                            <?php echo !empty($chiTiet['email_nguoi_xoa']) 
                                ? htmlspecialchars($chiTiet['email_nguoi_xoa']) 
                                : '<span class="text-muted">N/A</span>'; ?>
                        </p>
                        <p><strong>ID người xóa:</strong> <?php echo htmlspecialchars($chiTiet['nguoi_xoa_id'] ?? ''); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <strong>Thông tin nhà cung cấp tại thời điểm xóa</strong>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($thongTin)): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Tên đơn vị:</strong> <?php echo htmlspecialchars($thongTin['ten_don_vi'] ?? ''); ?></p>
                                    <p><strong>Loại dịch vụ:</strong> 
                                        <?php 
                                        $loai = $thongTin['loai_dich_vu'] ?? null;
                                        echo $loai ? ($loaiDichVuMap[$loai] ?? $loai) : 'N/A';
                                        ?>
                                    </p>
                                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($thongTin['dia_chi'] ?? ''); ?></p>
                                    <p><strong>Liên hệ:</strong> <?php echo htmlspecialchars($thongTin['lien_he'] ?? ''); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Mô tả chi tiết:</strong></p>
                                    <p><?php echo !empty($thongTin['mo_ta']) 
                                        ? nl2br(htmlspecialchars($thongTin['mo_ta'])) 
                                        : '<span class="text-muted">Không có</span>'; ?></p>

                                    <?php if (!empty($thongTin['ghi_chu'])): ?>
                                        <p><strong>Ghi chú nội bộ:</strong></p>
                                        <p><?php echo nl2br(htmlspecialchars($thongTin['ghi_chu'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Không lưu kèm thông tin chi tiết nhà cung cấp.</p>
                        <?php endif; ?>
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


