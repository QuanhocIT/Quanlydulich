<?php
$pageTitle = 'Quản lý Nhà cung cấp';
$currentPage = 'nhaCungCap';
ob_start();

$selectedSupplier = $selectedSupplier ?? null;
$serviceTypeSummary = $serviceTypeSummary ?? [];
$supplierStats = $supplierStats ?? [];
$supplierServices = $supplierServices ?? [];
$serviceTypes = $serviceTypes ?? [];
$selectedLoai = $selectedLoai ?? null;
$loaiDichVuMap = [
    'KhachSan' => 'Khách sạn',
    'NhaHang' => 'Nhà hàng',
    'Xe' => 'Xe vận chuyển',
    'Ve' => 'Vé máy bay / tàu',
    'Visa' => 'Visa',
    'BaoHiem' => 'Bảo hiểm',
    'Khac' => 'Khác'
];
$statusMap = [
    'ChoXacNhan' => ['Chờ xác nhận', 'warning'],
    'DaXacNhan' => ['Đã xác nhận', 'success'],
    'TuChoi' => ['Từ chối', 'danger'],
    'Huy' => ['Hủy', 'secondary'],
    'HoanTat' => ['Hoàn tất', 'info']
];
?>

<style>
    .page-header-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 40px;
        margin-bottom: 40px;
        backdrop-filter: blur(10px);
    }

    .info-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .info-card-header {
        background: rgba(212, 175, 55, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        color: var(--accent-gold);
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .info-card-body {
        padding: 25px;
    }

    .supplier-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .supplier-item {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: all 0.3s;
    }

    .supplier-item:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .supplier-item.active {
        background: rgba(212, 175, 55, 0.1);
        border-left: 3px solid var(--accent-gold);
    }

    .supplier-item:last-child {
        border-bottom: none;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-left: 4px solid;
        border-radius: 2px;
        padding: 25px;
        backdrop-filter: blur(10px);
    }

    .stat-card.border-primary { border-left-color: #0d6efd; }
    .stat-card.border-success { border-left-color: #198754; }
    .stat-card.border-danger { border-left-color: #dc3545; }
    .stat-card.border-info { border-left-color: #0dcaf0; }

    .stat-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-light);
        margin-bottom: 8px;
    }

    .stat-value.success { color: #198754; }
    .stat-value.danger { color: #dc3545; }
    .stat-value.info { color: #0dcaf0; }

    .stat-note {
        font-size: 11px;
        color: var(--text-muted);
    }

    .table-wrapper {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: rgba(212, 175, 55, 0.1);
    }

    .table th {
        padding: 15px;
        text-align: left;
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--accent-gold);
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        font-size: 13px;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-warning {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .badge-success {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .badge-danger {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .badge-secondary {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }

    .badge-info {
        background: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }

    .two-column-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .two-column-layout {
            grid-template-columns: 1fr;
        }
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border-color: rgba(25, 135, 84, 0.3);
        color: #198754;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    .alert-warning {
        background: rgba(255, 193, 7, 0.1);
        border-color: rgba(255, 193, 7, 0.3);
        color: #ffc107;
    }

    .alert-info {
        background: rgba(13, 202, 240, 0.1);
        border-color: rgba(13, 202, 240, 0.3);
        color: #0dcaf0;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .input,
    .form-group .select,
    .form-group textarea {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 12px 10px;
        font-size: 13px;
        border-radius: 2px;
        transition: all 0.3s;
        width: 100%;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-group .input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group .input:focus,
    .form-group .select:focus,
    .form-group textarea:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-gold);
    }

    .form-group .select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23d4af37' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: rgba(45, 45, 45, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        backdrop-filter: blur(10px);
    }

    .modal-lg .modal-content {
        max-width: 900px;
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header.bg-danger {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }

    .modal-title {
        margin: 0;
        color: var(--text-light);
        font-size: 18px;
        font-weight: 600;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-close {
        background: none;
        border: none;
        color: var(--text-light);
        font-size: 24px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .btn-close-white {
        color: white;
    }

    body.page-nhaCungCap .content-area {
        padding: 34px 48px 56px;
        background:
            radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
            radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
    }

    body.page-nhaCungCap .page-header-section {
        min-height: 154px;
        padding: 28px 34px;
        background:
            linear-gradient(100deg, rgba(28, 31, 33, 0.96) 0%, rgba(30, 42, 45, 0.94) 52%, rgba(119, 102, 45, 0.84) 100%),
            url("<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg");
        background-size: cover;
        background-position: center;
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 8px;
        box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
        position: relative;
        overflow: hidden;
    }

    body.page-nhaCungCap .page-header-section::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
        pointer-events: none;
    }

    body.page-nhaCungCap .page-header-section > div {
        position: relative;
        z-index: 2;
        align-items: center !important;
    }

    body.page-nhaCungCap .page-header-section h1 {
        margin: 0;
        color: #ffe082;
        font-size: 2rem;
        line-height: 1.18;
        letter-spacing: 0;
        text-shadow: none;
    }

    body.page-nhaCungCap .page-header-section p {
        color: rgba(255,255,255,0.86) !important;
        font-size: 1rem;
        text-shadow: none;
    }

    body.page-nhaCungCap .page-header-section .btn,
    body.page-nhaCungCap .info-card .btn,
    body.page-nhaCungCap .table-wrapper .btn {
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-nhaCungCap .page-header-section .btn {
        min-height: 46px;
        padding-inline: 22px;
    }

    body.page-nhaCungCap .two-column-layout {
        grid-template-columns: minmax(320px, .9fr) minmax(0, 1.8fr);
        gap: 38px;
    }

    body.page-nhaCungCap .info-card,
    body.page-nhaCungCap .stat-card,
    body.page-nhaCungCap .table-wrapper {
        background: rgba(28, 30, 31, 0.78);
        border-color: rgba(212, 175, 55, 0.22);
        border-radius: 8px;
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    body.page-nhaCungCap .info-card-header {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.18), rgba(13, 202, 240, 0.06));
        border-bottom-color: rgba(255,255,255,0.09);
        padding: 20px 24px;
        letter-spacing: 0.04em;
    }

    body.page-nhaCungCap .info-card-body {
        padding: 28px;
    }

    body.page-nhaCungCap .supplier-item {
        padding: 18px 20px;
        border-bottom-color: rgba(255,255,255,0.08);
        border-left: 3px solid transparent;
    }

    body.page-nhaCungCap .supplier-item:hover {
        background: rgba(255,255,255,0.055);
    }

    body.page-nhaCungCap .supplier-item.active {
        background: rgba(212, 175, 55, 0.12);
        border-left-color: var(--accent-gold);
    }

    body.page-nhaCungCap .stats-grid {
        gap: 16px;
    }

    body.page-nhaCungCap .stat-card {
        min-height: 132px;
        padding: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.025));
        border-left-width: 3px;
    }

    body.page-nhaCungCap .stat-value {
        font-size: 2.25rem;
        line-height: 1;
    }

    body.page-nhaCungCap .table-wrapper {
        overflow-x: auto;
    }

    body.page-nhaCungCap .table {
        min-width: 900px;
    }

    body.page-nhaCungCap .table thead {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.14), rgba(13, 202, 240, 0.06));
    }

    body.page-nhaCungCap .table th {
        padding: 16px 20px;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    body.page-nhaCungCap .table td {
        padding: 18px 20px;
        vertical-align: middle;
    }

    body.page-nhaCungCap .badge {
        border-radius: 8px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
    }

    body.page-nhaCungCap .form-group label {
        color: rgba(245,245,245,0.78);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-nhaCungCap .form-group .input,
    body.page-nhaCungCap .form-group .select,
    body.page-nhaCungCap .form-group textarea {
        min-height: 52px;
        border-radius: 8px;
        border-color: rgba(255,255,255,0.14);
        background-color: rgba(255,255,255,0.08);
    }

    body.page-nhaCungCap .modal-content {
        border-radius: 8px;
        background: rgba(28, 30, 31, 0.96);
        border-color: rgba(212, 175, 55, 0.22);
    }

    body.page-nhaCungCap .modal-header {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.18), rgba(13, 202, 240, 0.06));
    }

    @media (max-width: 1200px) {
        body.page-nhaCungCap .two-column-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 900px) {
        body.page-nhaCungCap .content-area {
            padding: 24px 18px 42px;
        }

        body.page-nhaCungCap .page-header-section {
            padding: 24px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>🏢 Quản lý Nhà cung cấp</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">Theo dõi đối tác khách sạn, nhà hàng, vận chuyển, vé, visa, bảo hiểm</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="btn btn-primary" onclick="document.getElementById('addSupplierModal').classList.add('show')">
                ➕ Thêm nhà cung cấp
            </button>
            <a href="index.php?act=admin/lichSuXoaNhaCungCap" class="btn btn-secondary">
                🕐 Lịch sử xóa
            </a>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash']['type'] ?? 'info'); ?>">
        <?php echo htmlspecialchars($_SESSION['flash']['message'] ?? ''); ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        ✓ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        ⚠ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="two-column-layout">
    <!-- Left Column: Supplier List -->
    <div>
        <div class="info-card">
            <div class="info-card-header">
                📋 Danh sách đối tác
            </div>
            <div class="info-card-body" style="padding: 0;">
                <?php if (empty($nhaCungCapList)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>Chưa có nhà cung cấp nào</p>
                    </div>
                <?php else: ?>
                    <ul class="supplier-list">
                        <?php foreach ($nhaCungCapList as $ncc): ?>
                        <li>
                            <a href="index.php?act=admin/nhaCungCap&id=<?php echo $ncc['id_nha_cung_cap']; ?>" 
                               class="supplier-item <?php echo ($selectedSupplier && $selectedSupplier['id_nha_cung_cap'] == $ncc['id_nha_cung_cap']) ? 'active' : ''; ?>"
                               style="display: block; text-decoration: none; color: inherit;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; margin-bottom: 5px; color: var(--text-light);">
                                            <?php echo htmlspecialchars($ncc['ten_don_vi'] ?? 'N/A'); ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            📍 <?php echo htmlspecialchars($ncc['dia_chi'] ?? 'Chưa cập nhật'); ?>
                                        </div>
                                    </div>
                                    <span class="badge badge-secondary" style="margin-left: 10px;">
                                        <?php echo $loaiDichVuMap[$ncc['loai_dich_vu']] ?? $ncc['loai_dich_vu']; ?>
                                    </span>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Supplier Details -->
    <div>
        <div class="info-card">
            <div class="info-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>ℹ️ Thông tin chi tiết</span>
                <?php if ($selectedSupplier): ?>
                <div style="display: flex; gap: 8px;">
                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById('viewSupplierModal<?php echo $selectedSupplier['id_nha_cung_cap']; ?>').classList.add('show')">
                        👁️ Xem
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById('editSupplierModal<?php echo $selectedSupplier['id_nha_cung_cap']; ?>').classList.add('show')">
                        ✏️ Sửa
                    </button>
                    <button class="btn btn-secondary btn-sm" style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);" onclick="document.getElementById('deleteSupplierModal<?php echo $selectedSupplier['id_nha_cung_cap']; ?>').classList.add('show')">
                        🗑️ Xóa
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="info-card-body">
                <?php if (!$selectedSupplier): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">↔️</div>
                        <p>Chọn một nhà cung cấp ở danh sách bên trái để xem chi tiết.</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                        <div>
                            <h4 style="margin-bottom: 15px; color: var(--text-light); font-size: 20px;">
                                <?php echo htmlspecialchars($selectedSupplier['ten_don_vi']); ?>
                            </h4>
                            <span class="badge badge-info" style="margin-bottom: 15px; display: inline-block;">
                                <?php echo $loaiDichVuMap[$selectedSupplier['loai_dich_vu']] ?? $selectedSupplier['loai_dich_vu']; ?>
                            </span>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                <li style="margin-bottom: 10px; color: var(--text-light); font-size: 13px;">
                                    📍 <?php echo htmlspecialchars($selectedSupplier['dia_chi'] ?? 'Chưa cập nhật'); ?>
                                </li>
                                <li style="margin-bottom: 10px; color: var(--text-light); font-size: 13px;">
                                    📞 <?php echo htmlspecialchars($selectedSupplier['lien_he'] ?? 'Chưa cập nhật'); ?>
                                </li>
                                <?php if ($selectedSupplier['danh_gia_tb']): ?>
                                <li style="margin-bottom: 10px; color: var(--text-light); font-size: 13px;">
                                    ⭐ Đánh giá TB: <?php echo number_format($selectedSupplier['danh_gia_tb'], 1); ?>/5
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div>
                            <h6 style="margin-bottom: 15px; color: var(--accent-gold); font-size: 14px;">Mô tả năng lực</h6>
                            <p style="color: var(--text-muted); line-height: 1.8; font-size: 13px; margin-bottom: 15px;">
                                <?php echo $selectedSupplier['mo_ta'] ? nl2br(htmlspecialchars($selectedSupplier['mo_ta'])) : 'Chưa có mô tả chi tiết.'; ?>
                            </p>
                            <?php if (!empty($serviceTypes)): ?>
                                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 10px; letter-spacing: 0.5px;">
                                    Danh mục dịch vụ đã cung ứng
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    <?php foreach ($serviceTypes as $type): ?>
                                        <span class="badge badge-secondary">
                                            <?php echo $loaiDichVuMap[$type] ?? $type; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($selectedSupplier): ?>
<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card border-primary">
        <div class="stat-label">Tổng dịch vụ</div>
        <div class="stat-value"><?php echo $supplierStats['tong_dich_vu'] ?? 0; ?></div>
        <div class="stat-note">Tính từ <?php echo $supplierStats['hop_tac_tu'] ? date('d/m/Y', strtotime($supplierStats['hop_tac_tu'])) : 'N/A'; ?></div>
    </div>
    <div class="stat-card border-success">
        <div class="stat-label">Đã xác nhận</div>
        <div class="stat-value success"><?php echo $supplierStats['da_xac_nhan'] ?? 0; ?></div>
        <div class="stat-note">Đang chờ: <?php echo $supplierStats['cho_xac_nhan'] ?? 0; ?></div>
    </div>
    <div class="stat-card border-info">
        <div class="stat-label">Giá trị hợp đồng</div>
        <div class="stat-value info">
            <?php echo number_format($supplierStats['tong_gia_tri'] ?? 0, 0, ',', '.'); ?>đ
        </div>
        <div class="stat-note">Cập nhật: <?php echo $supplierStats['moi_nhat'] ? date('d/m/Y', strtotime($supplierStats['moi_nhat'])) : 'N/A'; ?></div>
    </div>
    <div class="stat-card border-danger">
        <div class="stat-label">Từ chối/Hủy</div>
        <div class="stat-value danger"><?php echo $supplierStats['tu_choi'] ?? 0; ?></div>
        <div class="stat-note">Theo dõi để cải thiện chất lượng</div>
    </div>
</div>

<!-- Service Type Summary -->
<div class="info-card">
    <div class="info-card-header">
        📊 Thống kê theo loại dịch vụ
    </div>
    <div class="info-card-body">
        <?php if (empty($serviceTypeSummary)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>Nhà cung cấp chưa tham gia tour nào</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Loại dịch vụ</th>
                            <th>Lần cung cấp</th>
                            <th>Đã xác nhận</th>
                            <th>Giá trị</th>
                            <th>Từ ngày</th>
                            <th>Gần nhất</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serviceTypeSummary as $summary): ?>
                        <tr>
                            <td>
                                <span class="badge badge-secondary">
                                    <?php echo $loaiDichVuMap[$summary['loai_dich_vu']] ?? $summary['loai_dich_vu']; ?>
                                </span>
                            </td>
                            <td><?php echo $summary['so_lan_cung_cap']; ?></td>
                            <td><?php echo $summary['so_da_xac_nhan']; ?></td>
                            <td><?php echo number_format($summary['tong_doanh_thu'] ?? 0, 0, ',', '.'); ?>đ</td>
                            <td><?php echo $summary['lan_dau'] ? date('d/m/Y', strtotime($summary['lan_dau'])) : '-'; ?></td>
                            <td><?php echo $summary['lan_gan_nhat'] ? date('d/m/Y', strtotime($summary['lan_gan_nhat'])) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Service History -->
<div class="info-card">
    <div class="info-card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 14px; font-weight: 600; color: var(--accent-gold);">🕐 Lịch sử cung ứng dịch vụ</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Theo dõi các tour đã hợp tác với đối tác</div>
        </div>
        <form method="GET" action="index.php" style="display: flex; gap: 10px;">
            <input type="hidden" name="act" value="admin/nhaCungCap">
            <input type="hidden" name="id" value="<?php echo $selectedSupplier['id_nha_cung_cap']; ?>">
            <select name="loai" class="select" onchange="this.form.submit()" style="width: auto; min-width: 200px;">
                <option value="">Tất cả loại dịch vụ</option>
                <?php foreach ($serviceTypes as $type): ?>
                    <option value="<?php echo $type; ?>" <?php echo ($selectedLoai === $type) ? 'selected' : ''; ?>>
                        <?php echo $loaiDichVuMap[$type] ?? $type; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">🔍 Lọc</button>
        </form>
    </div>
    <div class="info-card-body" style="padding: 0;">
        <?php if (empty($supplierServices)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p>Chưa có dữ liệu cho bộ lọc này</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tour</th>
                            <th>Dịch vụ</th>
                            <th>Thời gian</th>
                            <th>Giá trị</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supplierServices as $service): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--text-light);"><?php echo htmlspecialchars($service['ten_tour'] ?? 'Tour chưa đặt tên'); ?></strong><br>
                                <small style="color: var(--text-muted); font-size: 11px;">LKH #<?php echo $service['lich_khoi_hanh_id'] ?? '-'; ?></small>
                            </td>
                            <td>
                                <span class="badge badge-secondary" style="margin-bottom: 5px; display: inline-block;">
                                    <?php echo $loaiDichVuMap[$service['loai_dich_vu']] ?? $service['loai_dich_vu']; ?>
                                </span><br>
                                <small style="color: var(--text-muted); font-size: 11px;"><?php echo htmlspecialchars($service['ten_dich_vu']); ?></small>
                            </td>
                            <td>
                                <?php echo $service['ngay_bat_dau'] ? date('d/m/Y', strtotime($service['ngay_bat_dau'])) : '-'; ?>
                                <?php if ($service['ngay_ket_thuc']): ?>
                                    <br><small style="color: var(--text-muted); font-size: 11px;">đến <?php echo date('d/m/Y', strtotime($service['ngay_ket_thuc'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($service['gia_tien']): ?>
                                    <strong style="color: var(--accent-gold);"><?php echo number_format($service['gia_tien'], 0, ',', '.'); ?>đ</strong>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">Đang cập nhật</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $status = $statusMap[$service['trang_thai']] ?? [$service['trang_thai'], 'secondary'];
                                ?>
                                <span class="badge badge-<?php echo $status[1]; ?>">
                                    <?php echo $status[0]; ?>
                                </span>
                            </td>
                            <td style="max-width: 200px;">
                                <?php echo $service['ghi_chu'] ? nl2br(htmlspecialchars($service['ghi_chu'])) : '<span style="color: var(--text-muted);">-</span>'; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="index.php?act=admin/chiTietDichVu&id=<?php echo $service['id']; ?>&ncc_id=<?php echo $selectedSupplier['id_nha_cung_cap'] ?? ''; ?>" 
                                       class="btn btn-secondary btn-sm" 
                                       style="background: rgba(13, 202, 240, 0.2); color: #0dcaf0; border-color: rgba(13, 202, 240, 0.3);"
                                       title="Xem chi tiết">
                                        👁️
                                    </a>
                                    <?php if ($service['trang_thai'] === 'ChoXacNhan'): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                style="background: rgba(25, 135, 84, 0.2); color: #198754; border-color: rgba(25, 135, 84, 0.3);"
                                                onclick="document.getElementById('approveServiceModal<?php echo $service['id']; ?>').classList.add('show')">
                                            ✓
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);"
                                                onclick="document.getElementById('rejectServiceModal<?php echo $service['id']; ?>').classList.add('show')">
                                            ✗
                                        </button>
                                    <?php endif; ?>
                                    <?php if (in_array($service['trang_thai'], ['ChoXacNhan', 'DaXacNhan'])): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                style="background: rgba(13, 110, 253, 0.2); color: #0d6efd; border-color: rgba(13, 110, 253, 0.3);"
                                                onclick="document.getElementById('updatePriceModal<?php echo $service['id']; ?>').classList.add('show')">
                                            ✏️
                                        </button>
                                    <?php endif; ?>
                                    <?php if (!empty($service['ghi_chu'])): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                onclick="document.getElementById('noteServiceModal<?php echo $service['id']; ?>').classList.add('show')">
                                            📝
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal Thêm nhà cung cấp -->
<div id="addSupplierModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h5 class="modal-title">Thêm Nhà cung cấp</h5>
            <button type="button" class="btn-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
        </div>
        <form method="POST" action="index.php?act=admin/addNhacungcap">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tài khoản đã đăng ký</label>
                        <select class="select" name="nguoi_dung_id" id="supplierUserSelect">
                            <option value="">-- Không gắn tài khoản --</option>
                            <?php if (!empty($supplierUsers)): ?>
                                <?php foreach ($supplierUsers as $user): ?>
                                    <option 
                                        value="<?php echo $user['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($user['ho_ten'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                        data-phone="<?php echo htmlspecialchars($user['so_dien_thoai'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        <?php echo htmlspecialchars($user['ho_ten'] ?? ''); ?> 
                                        (<?php echo htmlspecialchars($user['email'] ?? ''); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tên đơn vị <span style="color: #dc3545;">*</span></label>
                        <input type="text" class="input" name="ten_don_vi" id="tenDonViInput" required>
                    </div>
                    <div class="form-group">
                        <label>Loại dịch vụ</label>
                        <select class="select" name="loai_dich_vu">
                            <?php foreach ($loaiDichVuMap as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" class="input" name="dia_chi">
                    </div>
                    <div class="form-group">
                        <label>Liên hệ</label>
                        <input type="text" class="input" name="lien_he" id="lienHeInput">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Mô tả dịch vụ / năng lực</label>
                        <textarea class="textarea" name="mo_ta" rows="3" placeholder="VD: Cung cấp khách sạn 3-4 sao tại Hà Nội, Đà Nẵng, có thể cung ứng tối đa 50 phòng/ngày..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addSupplierModal').classList.remove('show')">Hủy</button>
                <button type="submit" class="btn btn-primary">Thêm</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($nhaCungCapList as $ncc): ?>
    <!-- Modal xem -->
    <div id="viewSupplierModal<?php echo $ncc['id_nha_cung_cap']; ?>" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h5 class="modal-title">Thông tin <?php echo htmlspecialchars($ncc['ten_don_vi']); ?></h5>
                <button type="button" class="btn-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Loại dịch vụ:</strong> <?php echo $loaiDichVuMap[$ncc['loai_dich_vu']] ?? $ncc['loai_dich_vu']; ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($ncc['dia_chi'] ?? 'Chưa cập nhật'); ?></p>
                <p><strong>Liên hệ:</strong> <?php echo htmlspecialchars($ncc['lien_he'] ?? 'Chưa cập nhật'); ?></p>
                <p><strong>Mô tả:</strong><br><?php echo $ncc['mo_ta'] ? nl2br(htmlspecialchars($ncc['mo_ta'])) : 'Chưa có mô tả'; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('show')">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Modal sửa -->
    <div id="editSupplierModal<?php echo $ncc['id_nha_cung_cap']; ?>" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật <?php echo htmlspecialchars($ncc['ten_don_vi']); ?></h5>
                <button type="button" class="btn-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
            </div>
            <form method="POST" action="index.php?act=admin/updateNhaCungCap">
                <div class="modal-body">
                    <input type="hidden" name="id_nha_cung_cap" value="<?php echo $ncc['id_nha_cung_cap']; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tên đơn vị <span style="color: #dc3545;">*</span></label>
                            <input type="text" class="input" name="ten_don_vi" value="<?php echo htmlspecialchars($ncc['ten_don_vi']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Loại dịch vụ</label>
                            <select class="select" name="loai_dich_vu">
                                <?php foreach ($loaiDichVuMap as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($ncc['loai_dich_vu'] === $key) ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Địa chỉ</label>
                            <input type="text" class="input" name="dia_chi" value="<?php echo htmlspecialchars($ncc['dia_chi'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Liên hệ</label>
                            <input type="text" class="input" name="lien_he" value="<?php echo htmlspecialchars($ncc['lien_he'] ?? ''); ?>">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Mô tả</label>
                            <textarea class="textarea" name="mo_ta" rows="3"><?php echo htmlspecialchars($ncc['mo_ta'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('show')">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal xóa nhà cung cấp -->
    <div id="deleteSupplierModal<?php echo $ncc['id_nha_cung_cap']; ?>" class="modal">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">⚠️ Xác nhận xóa nhà cung cấp</h5>
                <button type="button" class="btn-close btn-close-white" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
            </div>
            <form method="POST" action="index.php?act=admin/deleteNhaCungCap">
                <div class="modal-body">
                    <input type="hidden" name="id_nha_cung_cap" value="<?php echo $ncc['id_nha_cung_cap']; ?>">
                    <div class="alert alert-warning">
                        ⚠️ <strong>Bạn có chắc chắn muốn xóa nhà cung cấp này?</strong>
                    </div>
                    <p><strong>Tên đơn vị:</strong> <?php echo htmlspecialchars($ncc['ten_don_vi']); ?></p>
                    <p><strong>Loại dịch vụ:</strong> <?php echo $loaiDichVuMap[$ncc['loai_dich_vu']] ?? $ncc['loai_dich_vu']; ?></p>
                    <div class="alert alert-danger" style="margin-top: 15px;">
                        <small>
                            ℹ️ <strong>Lưu ý:</strong> Hành động này sẽ xóa vĩnh viễn nhà cung cấp và tất cả dữ liệu liên quan bao gồm:
                            <ul style="margin: 10px 0 0 20px; padding: 0;">
                                <li>Phân bổ dịch vụ đã cung cấp</li>
                                <li>Danh mục dịch vụ của nhà cung cấp</li>
                                <?php if (!empty($ncc['nguoi_dung_id'])): ?>
                                <li>Vai trò của tài khoản sẽ được đổi về "Khách hàng"</li>
                                <?php endif; ?>
                            </ul>
                        </small>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label>🔒 Mật khẩu Admin <span style="color: #dc3545;">*</span></label>
                        <input type="password" class="input" name="mat_khau" placeholder="Nhập mật khẩu để xác nhận" required autofocus>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                            Vui lòng nhập mật khẩu admin để xác nhận xóa
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 Lý do xóa (tùy chọn)</label>
                        <textarea class="textarea" name="ly_do_xoa" rows="3" placeholder="Nhập lý do xóa nhà cung cấp này..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('show')">Hủy</button>
                    <button type="submit" class="btn btn-secondary" style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);">
                        🗑️ Xác nhận xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!empty($supplierServices)): ?>
    <?php foreach ($supplierServices as $service): ?>
        <?php $serviceId = $service['id']; ?>
        <?php if ($service['trang_thai'] === 'ChoXacNhan'): ?>
            <!-- Approve modal -->
            <div id="approveServiceModal<?php echo $serviceId; ?>" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận dịch vụ</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
                    </div>
                    <form method="POST" action="index.php?act=admin/supplierServiceAction">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="dich_vu_id" value="<?php echo $serviceId; ?>">
                            <input type="hidden" name="ncc_id" value="<?php echo $selectedSupplier['id_nha_cung_cap'] ?? ''; ?>">
                            <p><strong>Tour:</strong> <?php echo htmlspecialchars($service['ten_tour'] ?? 'N/A'); ?></p>
                            <p><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($service['ten_dich_vu']); ?></p>
                            <div class="form-group">
                                <label>Giá phê duyệt (VND)</label>
                                <input type="number" class="input" name="gia_tien" min="0" step="1000" value="<?php echo $service['gia_tien'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('show')">Hủy</button>
                            <button type="submit" class="btn btn-primary">Xác nhận</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reject modal -->
            <div id="rejectServiceModal<?php echo $serviceId; ?>" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Từ chối dịch vụ</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
                    </div>
                    <form method="POST" action="index.php?act=admin/supplierServiceAction">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="dich_vu_id" value="<?php echo $serviceId; ?>">
                            <input type="hidden" name="ncc_id" value="<?php echo $selectedSupplier['id_nha_cung_cap'] ?? ''; ?>">
                            <p><strong>Tour:</strong> <?php echo htmlspecialchars($service['ten_tour'] ?? 'N/A'); ?></p>
                            <p><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($service['ten_dich_vu']); ?></p>
                            <div class="form-group">
                                <label>Lý do / ghi chú</label>
                                <textarea class="textarea" name="ghi_chu" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('show')">Hủy</button>
                            <button type="submit" class="btn btn-secondary" style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);">Từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if (in_array($service['trang_thai'], ['ChoXacNhan', 'DaXacNhan'])): ?>
            <!-- Update price modal -->
            <div id="updatePriceModal<?php echo $serviceId; ?>" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cập nhật giá dịch vụ</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
                    </div>
                    <form method="POST" action="index.php?act=admin/supplierServiceAction">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="update_price">
                            <input type="hidden" name="dich_vu_id" value="<?php echo $serviceId; ?>">
                            <input type="hidden" name="ncc_id" value="<?php echo $selectedSupplier['id_nha_cung_cap'] ?? ''; ?>">
                            <p><strong>Tour:</strong> <?php echo htmlspecialchars($service['ten_tour'] ?? 'N/A'); ?></p>
                            <p><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($service['ten_dich_vu']); ?></p>
                            <div class="form-group">
                                <label>Giá mới (VND)</label>
                                <input type="number" class="input" name="gia_tien" min="0" step="1000" value="<?php echo $service['gia_tien'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('show')">Hủy</button>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($service['ghi_chu'])): ?>
            <!-- Note modal -->
            <div id="noteServiceModal<?php echo $serviceId; ?>" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ghi chú dịch vụ</h5>
                        <button type="button" class="btn-close" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <?php echo nl2br(htmlspecialchars($service['ghi_chu'])); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').classList.remove('show')">Đóng</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    // Auto-fill form from user select
    (function() {
        const selectEl = document.getElementById('supplierUserSelect');
        if (!selectEl) return;
        const tenDonViInput = document.getElementById('tenDonViInput');
        const lienHeInput = document.getElementById('lienHeInput');

        selectEl.addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            if (!option || !option.value) {
                return;
            }
            const name = option.getAttribute('data-name') || '';
            const phone = option.getAttribute('data-phone') || '';
            if (tenDonViInput && !tenDonViInput.value) {
                tenDonViInput.value = name;
            }
            if (lienHeInput && !lienHeInput.value && phone) {
                lienHeInput.value = phone;
            }
        });
    })();

    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
