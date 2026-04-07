<?php
$currentTab = $currentTab ?? '';
$supplierName = htmlspecialchars($nhaCungCap['ten_don_vi'] ?? 'Nhà cung cấp');
?>
<div class="supplier-topbar">
    <div class="supplier-topbar-inner">
        <div class="supplier-brand">
            <div class="supplier-brand-badge">
                <i class="bi bi-buildings"></i>
            </div>
            <div>
                <span class="supplier-brand-label">Supplier Portal</span>
                <span class="supplier-brand-title"><?php echo $supplierName; ?></span>
            </div>
        </div>
        <ul class="supplier-nav-list">
            <li><a class="supplier-nav-link <?php echo $currentTab === 'dashboard' ? 'active' : ''; ?>" href="index.php?act=nhaCungCap/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a class="supplier-nav-link <?php echo $currentTab === 'baoGia' ? 'active' : ''; ?>" href="index.php?act=nhaCungCap/baoGia"><i class="bi bi-file-earmark-text"></i> Báo giá</a></li>
            <li><a class="supplier-nav-link <?php echo $currentTab === 'dichVu' ? 'active' : ''; ?>" href="index.php?act=nhaCungCap/dichVu"><i class="bi bi-briefcase"></i> Dịch vụ</a></li>
            <li><a class="supplier-nav-link <?php echo $currentTab === 'congNo' ? 'active' : ''; ?>" href="index.php?act=nhaCungCap/congNo"><i class="bi bi-cash-stack"></i> Công nợ</a></li>
            <li><a class="supplier-nav-link <?php echo $currentTab === 'hopDong' ? 'active' : ''; ?>" href="index.php?act=nhaCungCap/hopDong"><i class="bi bi-clock-history"></i> Hợp tác</a></li>
            <li><a class="supplier-nav-link logout-link" href="index.php?act=auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
        </ul>
    </div>
</div>
