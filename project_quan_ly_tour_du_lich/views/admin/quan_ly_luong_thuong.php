<?php
$pageTitle = $pageTitle ?? 'Quản lý lương thưởng nhân sự';
$currentPage = $currentPage ?? 'luongThuong';

$filterNhanSu = $filterNhanSu ?? ($_GET['nhan_su_id'] ?? '');
$filterTour = $filterTour ?? ($_GET['tour_id'] ?? '');
$filterMonth = $filterMonth ?? ($_GET['month'] ?? '');
$filterYear = $filterYear ?? ($_GET['year'] ?? '');
$filterTrangThaiLuong = $filterTrangThaiLuong ?? ($_GET['trang_thai_luong'] ?? '');
$showAll = $showAll ?? (($_GET['all'] ?? '') === '1');

$allLuongTongHop = $allLuongTongHop ?? [];
$nhanSuList = $nhanSuList ?? [];
$tourList = $tourList ?? [];
$lichKhoiHanhList = $lichKhoiHanhList ?? [];

ob_start();
?>
<style>
    .luong-container {
        max-width: 100%;
        margin: 0;
        background: transparent;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
    }
    .luong-header {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        display: flex;
        align-items: center;
        gap: 22px;
        overflow: hidden;
        flex-wrap: wrap;
    }
    .luong-header-glow {
        position: absolute;
        top: 0; left: -60%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: lhglow 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }
    @keyframes lhglow {
        0% { left: -60%; }
        100% { left: 100%; }
    }
    .luong-header-avatar {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        z-index: 2;
        flex-shrink: 0;
    }
    .luong-header-text {
        flex: 1;
        z-index: 2;
    }
    .luong-header-actions {
        z-index: 2;
    }
    .luong-title {
        font-size: 1.7rem;
        font-weight: 900;
        margin: 0;
        color: #ffe082;
        letter-spacing: 1px;
        line-height: 1.2;
        text-shadow: 0 2px 8px #2d2d2d;
    }
    .luong-subtitle {
        color: #fffde7;
        font-size: 1rem;
        margin-top: 6px;
        text-shadow: 0 1px 4px #2d2d2d;
    }
    .flash {
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 14px;
        font-size: 14px;
    }
    .flash-success { background: rgba(0, 230, 118, 0.14); border: 1px solid rgba(0, 230, 118, 0.35); color: #b7ffda; }
    .flash-error { background: rgba(255, 82, 82, 0.14); border: 1px solid rgba(255, 82, 82, 0.35); color: #ffd0d0; }

    .luong-filter-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        padding: 18px 22px 14px 22px;
        box-shadow: none;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
    }
    .luong-filter-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 10px 12px;
        align-items: end;
    }
    .luong-filter-grid .form-group {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .luong-filter-grid label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 5px;
        font-weight: 600;
    }
    .luong-filter-grid select,
    .luong-filter-grid input[type=number],
    .luong-filter-grid input[type=text] {
        border-radius: 4px;
        border: 1px solid rgba(255,255,255,0.15);
        background: rgba(0,0,0,0.2);
        color: var(--text-light);
        padding: 8px 10px;
        font-size: 13px;
        transition: border-color 0.2s;
    }
    .luong-filter-grid select:focus,
    .luong-filter-grid input:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
    }
    .btn-gold {
        background: var(--accent-gold, #ffd700);
        color: #222;
        font-weight: bold;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
    }
    .btn-gold:hover { background: #e6c200; }

    .luong-table {
        background: rgba(45, 45, 45, 0.5);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: none;
        border: 1px solid rgba(212,175,55,0.18);
        backdrop-filter: blur(10px);
    }
    .luong-table th, .luong-table td {
        text-align: center;
        vertical-align: middle;
        padding: 12px 8px;
    }
    .luong-table th {
        background: rgba(212,175,55,0.1);
        color: var(--accent-gold, #ffd700);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .luong-table tr { border-bottom: 1px solid rgba(255,255,255,0.05); }
    .luong-table tbody tr:hover { background: rgba(255,255,255,0.04); }
    .luong-table .fw-bold { color: #10b981 !important; font-weight: bold; }
    @media (max-width: 900px) {
        .luong-title { font-size: 1.25rem; }
        .luong-filter-grid { grid-template-columns: repeat(6, 1fr); }
        .luong-table th, .luong-table td { padding: 6px 2px; font-size: 12px; }
    }
    @media (max-width: 700px) {
        .luong-filter-grid { grid-template-columns: repeat(2, 1fr); }
        .luong-filter-grid .form-group { grid-column: span 2 !important; }
    }
</style>

<div class="luong-container">
    <div class="luong-header">
        <div class="luong-header-glow"></div>
        <div class="luong-header-avatar">💰</div>
        <div class="luong-header-text">
            <div class="luong-title">Quản lý lương thưởng</div>
            <div class="luong-subtitle">Theo tháng/năm hoặc xem tất cả, hỗ trợ tính lại/duyệt/thanh toán.</div>
        </div>
        <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
        <div class="luong-header-actions">
            <button id="showCreateLuongBtn" class="btn-gold"><i class="bi bi-plus-circle me-1"></i> Tạo lương/thưởng</button>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="flash flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="flash flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
        <style>
        .tao-luong-form-wrap { display: none; justify-content: center; margin-bottom: 18px; }
        .tao-luong-form {
            background: #181818;
            border-radius: 12px;
            box-shadow: 0 2px 16px #0003;
            padding: 24px 22px 14px 22px;
            max-width: 980px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 14px 14px;
            justify-content: center;
            align-items: flex-end;
        }
        .tao-luong-form .form-group { flex: 1 1 180px; min-width: 180px; display: flex; flex-direction: column; margin-bottom: 0; }
        .tao-luong-form label { color: var(--accent-gold, #ffd700); font-size: 14px; font-weight: 600; margin-bottom: 6px; }
        .tao-luong-form input, .tao-luong-form select { border-radius: 4px; border: 1px solid #444; background: #222; color: #fff; padding: 7px 10px; font-size: 14px; }
        .tao-luong-form button { padding: 10px 18px; font-size: 14px; letter-spacing: 0.5px; }
        </style>
        <div id="createLuongFormWrap" class="tao-luong-form-wrap">
            <form class="tao-luong-form" method="post" action="index.php?act=admin/taoLuongThuong">
                <div class="form-group">
                    <label>Nhân sự <span style="color:red">*</span></label>
                    <select name="nhan_su_id" required>
                        <option value="">-- Chọn --</option>
                        <?php foreach ($nhanSuList as $ns): ?>
                            <option value="<?php echo (int)$ns['nhan_su_id']; ?>"><?php echo htmlspecialchars($ns['ho_ten'] ?? ''); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lịch khởi hành <span style="color:red">*</span></label>
                    <select name="lich_khoi_hanh_id" required>
                        <option value="">-- Chọn --</option>
                        <?php foreach ($lichKhoiHanhList as $lk): ?>
                            <option value="<?php echo (int)$lk['id']; ?>">
                                #<?php echo (int)$lk['id']; ?> - <?php echo htmlspecialchars($lk['ten_tour'] ?? ''); ?>
                                (<?php echo !empty($lk['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($lk['ngay_khoi_hanh'])) : ''; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Loại lương</label>
                    <select name="loai_luong">
                        <option value="CoDinh">Cố định</option>
                        <option value="PhanTram">Phần trăm</option>
                        <option value="KetHop">Kết hợp</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lương cố định</label>
                    <input type="number" name="so_tien_co_dinh" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>% Hoa hồng</label>
                    <input type="number" name="phan_tram_hoa_hong" min="0" max="100" step="0.01" value="0">
                </div>
                <div class="form-group">
                    <label>Ghi chú</label>
                    <input type="text" name="ghi_chu" maxlength="255">
                </div>
                <div class="form-group" style="align-items:flex-end;">
                    <button type="submit" class="btn-gold">Tạo lương/thưởng</button>
                </div>
            </form>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('showCreateLuongBtn');
                const formWrap = document.getElementById('createLuongFormWrap');
                if (btn && formWrap) {
                    btn.addEventListener('click', function() {
                        formWrap.style.display = (formWrap.style.display === 'none' || formWrap.style.display === '') ? 'flex' : 'none';
                    });
                }
            });
        </script>
    <?php endif; ?>

    <div class="luong-filter-card">
        <form method="get" action="index.php">
            <input type="hidden" name="act" value="admin/quanLyLuongThuong">
            <div class="luong-filter-grid">
                <div class="form-group" style="grid-column: span 3;">
                    <label>Nhân sự</label>
                    <select name="nhan_su_id">
                        <option value="">Tất cả</option>
                        <?php foreach ($nhanSuList as $ns): ?>
                            <option value="<?php echo (int)$ns['nhan_su_id']; ?>" <?php echo ((string)$filterNhanSu === (string)$ns['nhan_su_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ns['ho_ten'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 4;">
                    <label>Tour</label>
                    <select name="tour_id">
                        <option value="">Tất cả</option>
                        <?php foreach ($tourList as $t): ?>
                            <option value="<?php echo (int)$t['tour_id']; ?>" <?php echo ((string)$filterTour === (string)$t['tour_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['ten_tour'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Tháng</label>
                    <select name="month" <?php echo $showAll ? 'disabled' : ''; ?>>
                        <option value="">--</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ((string)$filterMonth === (string)$m) ? 'selected' : ''; ?>><?php echo $m; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 3;">
                    <label>Năm</label>
                    <input type="number" name="year" value="<?php echo htmlspecialchars((string)$filterYear); ?>" min="2000" max="2100" <?php echo $showAll ? 'disabled' : ''; ?>>
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label>Trạng thái lương</label>
                    <select name="trang_thai_luong">
                        <option value="">Tất cả</option>
                        <option value="ChoDuyet" <?php echo ($filterTrangThaiLuong === 'ChoDuyet') ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="DaDuyet" <?php echo ($filterTrangThaiLuong === 'DaDuyet') ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="DaThanhToan" <?php echo ($filterTrangThaiLuong === 'DaThanhToan') ? 'selected' : ''; ?>>Đã thanh toán</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column: span 4;">
                    <label for="searchNhanSu"><i class="bi bi-search me-1"></i>Tìm nhanh</label>
                    <input type="text" id="searchNhanSu" placeholder="Nhập tên nhân sự..." style="width:100%;">
                </div>

                <div class="form-group" style="grid-column: span 5; display:flex; flex-direction:row; justify-content:flex-end; gap:8px; align-items:center;">
                    <button type="submit" class="btn-gold"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    <a class="btn-gold" href="index.php?act=admin/quanLyLuongThuong&all=1" style="background:#333;color:#fff;"><i class="bi bi-list-ul me-1"></i>Tất cả</a>
                </div>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchNhanSu');
        const tableBody = document.querySelector('.luong-table tbody');
        if (!searchInput || !tableBody) return;
        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();
            Array.from(tableBody.rows).forEach(row => {
                const nameCell = row.cells[0];
                if (!nameCell) return;
                const name = nameCell.textContent.toLowerCase();
                row.style.display = name.includes(keyword) ? '' : 'none';
            });
        });
    });
    </script>

    <div class="table-responsive">
        <table class="table luong-table">
            <thead>
                <tr>
                    <th><i class="bi bi-person-badge me-1"></i>Nhân sự</th>
                    <th><i class="bi bi-person-lines-fill me-1"></i>Vai trò</th>
                    <th><i class="bi bi-briefcase me-1"></i>Số tour</th>
                    <th><i class="bi bi-wallet2 me-1"></i>Loại lương</th>
                    <th><i class="bi bi-cash me-1"></i>Lương cố định</th>
                    <th><i class="bi bi-gift me-1"></i>Tiền hoa hồng</th>
                    <th><i class="bi bi-calculator me-1"></i>Tổng lương</th>
                    <th><i class="bi bi-clock-history me-1"></i>Trạng thái lương</th>
                    <th><i class="bi bi-calendar-check me-1"></i>Cập nhật</th>
                    <th><i class="bi bi-eye me-1"></i>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($allLuongTongHop)): ?>
                    <?php foreach ($allLuongTongHop as $row): ?>
                        <?php
                            $nhanSuId = (int)($row['nhan_su_id'] ?? 0);
                            $detailHref = 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId;
                            if ($showAll) {
                                $detailHref .= '&all=1';
                            } else {
                                $detailHref .= '&month=' . urlencode((string)$filterMonth) . '&year=' . urlencode((string)$filterYear);
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ho_ten'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['vai_tro'] ?? ''); ?></td>
                            <td><?php echo (int)($row['so_dong'] ?? 0); ?></td>
                            <td>Tổng hợp</td>
                            <td><?php echo number_format((float)($row['tong_co_dinh'] ?? 0)); ?></td>
                            <td><?php echo number_format((float)($row['tong_hoa_hong'] ?? 0)); ?></td>
                            <td class="fw-bold"><?php echo number_format((float)($row['tong_luong'] ?? 0)); ?></td>
                            <td><?php echo htmlspecialchars($row['trang_thai_luong_tong_hop'] ?? ''); ?></td>
                            <td><?php echo !empty($row['ngay_cap_nhat_luong']) ? date('d/m/Y H:i', strtotime($row['ngay_cap_nhat_luong'])) : ''; ?></td>
                            <td>
                                <button
                                    type="button"
                                    class="btn-gold"
                                    onclick='window.location.assign(<?php echo json_encode($detailHref, JSON_UNESCAPED_UNICODE); ?>);'
                                    style="padding: 6px 12px;"
                                >Xem</button>
                                <?php if (!$showAll && !empty($filterMonth) && !empty($filterYear) && (($_SESSION['role'] ?? '') === 'Admin')): ?>
                                    <div style="margin-top:8px; display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                                        <form method="post" action="index.php?act=admin/tinhLaiLuongNhanSu" style="margin:0;">
                                            <input type="hidden" name="nhan_su_id" value="<?php echo $nhanSuId; ?>">
                                            <input type="hidden" name="month" value="<?php echo (int)$filterMonth; ?>">
                                            <input type="hidden" name="year" value="<?php echo (int)$filterYear; ?>">
                                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'index.php?act=admin/quanLyLuongThuong'); ?>">
                                            <button type="submit" class="btn-gold" style="padding:6px 10px; background:#333; color:#fff;">
                                                <i class="bi bi-arrow-repeat me-1"></i>Tính lại
                                            </button>
                                        </form>
                                        <form method="post" action="index.php?act=admin/duyetLuongNhanSu" style="margin:0;">
                                            <input type="hidden" name="nhan_su_id" value="<?php echo $nhanSuId; ?>">
                                            <input type="hidden" name="month" value="<?php echo (int)$filterMonth; ?>">
                                            <input type="hidden" name="year" value="<?php echo (int)$filterYear; ?>">
                                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'index.php?act=admin/quanLyLuongThuong'); ?>">
                                            <button type="submit" class="btn-gold" style="padding:6px 10px;">
                                                <i class="bi bi-check2-circle me-1"></i>Duyệt
                                            </button>
                                        </form>
                                        <form method="post" action="index.php?act=admin/thanhToanLuongNhanSu" style="margin:0;">
                                            <input type="hidden" name="nhan_su_id" value="<?php echo $nhanSuId; ?>">
                                            <input type="hidden" name="month" value="<?php echo (int)$filterMonth; ?>">
                                            <input type="hidden" name="year" value="<?php echo (int)$filterYear; ?>">
                                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'index.php?act=admin/quanLyLuongThuong'); ?>">
                                            <button type="submit" class="btn-gold" style="padding:6px 10px; background:#00e676;">
                                                <i class="bi bi-cash-coin me-1"></i>TT
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
