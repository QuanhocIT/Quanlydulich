<?php 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php?act=auth/login');
    exit;
}
$pageTitle = 'Lịch sử xóa nhà cung cấp';
$currentPage = 'nhaCungCap';
ob_start();
?>
<style>
        .info-box {
            background: rgba(45, 45, 45, 0.5);
            border-left: 4px solid var(--accent-gold);
            padding: 10px;
            margin-bottom: 10px;
            backdrop-filter: blur(10px);
        }
        .supplier-info {
            font-size: 0.9em;
            color: var(--text-light);
        }
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card-body {
            color: var(--text-light);
        }
        .table {
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .table-light {
            background: rgba(45, 45, 45, 0.7) !important;
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
        .bg-info {
            background: rgba(0, 123, 255, 0.3) !important;
            color: #4da3ff !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .text-danger {
            color: #dc3545 !important;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .btn-outline-primary {
            background: transparent;
            color: #4da3ff;
            border: 1px solid rgba(13, 110, 253, 0.5);
        }
        .btn-outline-primary:hover {
            background: rgba(13, 110, 253, 0.3);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.5);
            color: #5cb85c;
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
                <i class="bi bi-clock-history" style="color: #dc3545;"></i> Lịch sử xóa nhà cung cấp
            </h1>
            <a href="index.php?act=admin/nhaCungCap" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($lichSuXoa)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Nhà cung cấp ID</th>
                                    <th>Tên đơn vị</th>
                                    <th>Loại dịch vụ</th>
                                    <th>Thông tin chi tiết</th>
                                    <th>Người xóa</th>
                                    <th>Lý do xóa</th>
                                    <th>Thời gian xóa</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $loaiDichVuMap = [
                                    'KhachSan' => 'Khách sạn',
                                    'NhaHang' => 'Nhà hàng',
                                    'Xe' => 'Xe vận chuyển',
                                    'Ve' => 'Vé máy bay / tàu',
                                    'Visa' => 'Visa',
                                    'BaoHiem' => 'Bảo hiểm',
                                    'Khac' => 'Khác'
                                ];
                                foreach ($lichSuXoa as $idx => $item): ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td>
                                            <span class="badge bg-secondary">#<?php echo $item['nha_cung_cap_id'] ?? 'N/A'; ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $thongTin = json_decode($item['thong_tin_nha_cung_cap'] ?? '{}', true);
                                            if ($thongTin && isset($thongTin['ten_don_vi'])): ?>
                                                <strong><?php echo htmlspecialchars($thongTin['ten_don_vi']); ?></strong>
                                            <?php elseif ($item['ten_nha_cung_cap']): ?>
                                                <strong><?php echo htmlspecialchars($item['ten_nha_cung_cap']); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">Nhà cung cấp đã bị xóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $thongTin = json_decode($item['thong_tin_nha_cung_cap'] ?? '{}', true);
                                            $loaiDichVu = $thongTin['loai_dich_vu'] ?? null;
                                            if ($loaiDichVu): ?>
                                                <span class="badge bg-info"><?php echo $loaiDichVuMap[$loaiDichVu] ?? $loaiDichVu; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $thongTin = json_decode($item['thong_tin_nha_cung_cap'] ?? '{}', true);
                                            if ($thongTin):
                                            ?>
                                                <div class="supplier-info">
                                                    <?php if (isset($thongTin['dia_chi']) && $thongTin['dia_chi']): ?>
                                                        <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($thongTin['dia_chi']); ?><br>
                                                    <?php endif; ?>
                                                    <?php if (isset($thongTin['lien_he']) && $thongTin['lien_he']): ?>
                                                        <strong>Liên hệ:</strong> <?php echo htmlspecialchars($thongTin['lien_he']); ?><br>
                                                    <?php endif; ?>
                                                    <?php if (isset($thongTin['mo_ta']) && $thongTin['mo_ta']): ?>
                                                        <strong>Mô tả:</strong> 
                                                        <small><?php echo mb_substr(htmlspecialchars($thongTin['mo_ta']), 0, 100); ?><?php echo mb_strlen($thongTin['mo_ta']) > 100 ? '...' : ''; ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Không có thông tin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['nguoi_xoa']): ?>
                                                <strong><?php echo htmlspecialchars($item['nguoi_xoa']); ?></strong>
                                                <?php if ($item['email_nguoi_xoa']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($item['email_nguoi_xoa']); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['ly_do_xoa']): ?>
                                                <span class="text-danger"><?php echo nl2br(htmlspecialchars($item['ly_do_xoa'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Không có</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo $item['thoi_gian_xoa'] ? date('d/m/Y H:i:s', strtotime($item['thoi_gian_xoa'])) : 'N/A'; ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <a href="index.php?act=admin/chiTietLichSuXoaNhaCungCap&id=<?php echo $item['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-search"></i> Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 opacity-25"></i>
                        <p class="mt-3">Chưa có lịch sử xóa nhà cung cấp nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>



