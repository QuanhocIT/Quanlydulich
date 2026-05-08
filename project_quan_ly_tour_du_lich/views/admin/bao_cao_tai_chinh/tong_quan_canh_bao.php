<?php
$pageTitle = 'Tổng quan các dự toán có cảnh báo';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
        .report-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 25px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            padding: 15px;
            text-align: left;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .bg-danger {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        .bg-warning {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }
        .text-dark {
            color: var(--text-light) !important;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .alert-info {
            background: rgba(0, 123, 255, 0.2);
            border: 1px solid rgba(0, 123, 255, 0.5);
            color: #4da3ff;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .btn-info {
            background: rgba(0, 123, 255, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(0, 123, 255, 0.5);
        }
        .btn-info:hover {
            background: rgba(0, 123, 255, 0.5);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .mt-3 {
            margin-top: 1rem;
        }
        body.page-baoCaoTaiChinh .content-area {
            padding: 34px 48px 56px;
            background:
                radial-gradient(circle at 12% 8%, rgba(32, 178, 170, 0.08), transparent 28%),
                radial-gradient(circle at 84% 16%, rgba(212, 175, 55, 0.10), transparent 30%),
                linear-gradient(135deg, #131616 0%, #181b1c 48%, #111313 100%);
        }
        body.page-baoCaoTaiChinh .finance-warning-page {
            max-width: 1080px !important;
            padding: 0 !important;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .page-header-section {
            min-height: 150px;
            padding: 28px 34px !important;
            border: 1px solid rgba(212, 175, 55, 0.22);
            border-radius: 8px;
            background:
                linear-gradient(90deg, rgba(22,24,24,.94) 0%, rgba(34,34,25,.9) 54%, rgba(212,175,55,.48) 100%),
                url('<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg') center/cover;
            box-shadow: 0 22px 48px rgba(0,0,0,.26);
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .page-header-section h1 {
            color: #ffe082 !important;
            font-size: 2.05rem !important;
            letter-spacing: 0 !important;
            line-height: 1.3;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .report-card,
        body.page-baoCaoTaiChinh .finance-warning-page .alert {
            background: rgba(28, 30, 31, .80);
            border: 1px solid rgba(212, 175, 55, .20);
            border-radius: 8px;
            box-shadow: 0 18px 38px rgba(0,0,0,.20);
        }
        body.page-baoCaoTaiChinh .finance-warning-page .report-card {
            overflow-x: auto;
            padding: 30px;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .table {
            min-width: 880px;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .table th {
            background: linear-gradient(90deg, rgba(212,175,55,.16), rgba(255,255,255,.04));
            color: #d4af37;
            padding: 18px 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: .84rem;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .table td {
            padding: 20px 16px;
            vertical-align: middle;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .badge {
            border-radius: 8px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            min-height: 34px;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .btn-info {
            min-height: 44px;
            border-radius: 8px;
            background: rgba(13,110,253,.18);
            color: #68b5ff;
            font-weight: 700;
        }
        body.page-baoCaoTaiChinh .finance-warning-page .alert-info {
            background: rgba(13, 110, 253, .12);
            color: #68b5ff;
            padding: 20px 22px;
            font-weight: 600;
        }
        @media (max-width: 992px) {
            body.page-baoCaoTaiChinh .content-area { padding: 24px 18px 44px; }
            body.page-baoCaoTaiChinh .finance-warning-page .page-header-section h1 { font-size: 1.55rem !important; }
        }
    </style>

<div class="finance-warning-page" style="padding: 20px; max-width: 900px; margin: 0 auto;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="fa fa-exclamation-triangle" style="color: #dc3545;"></i> Tổng quan các dự toán có cảnh báo
        </h1>
    </div>
    
    <div class="report-card">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên tour</th>
                    <th>Loại cảnh báo</th>
                    <th>Chi phí thực tế</th>
                    <th>Dự toán</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr data-href="index.php?act=admin/soSanhDuToan&du_toan_id=1">
                    <td>1</td>
                    <td>Tour Đà Nẵng 3N2Đ</td>
                    <td><span class="badge bg-danger">Vượt dự toán</span></td>
                    <td>120,000,000 đ</td>
                    <td>100,000,000 đ</td>
                    <td><a href="index.php?act=admin/soSanhDuToan&du_toan_id=1" class="btn btn-info btn-sm">Xem chi tiết</a></td>
                </tr>
                <tr data-href="index.php?act=admin/soSanhDuToan&du_toan_id=2">
                    <td>2</td>
                    <td>Tour Sapa 2N1Đ</td>
                    <td><span class="badge bg-warning text-dark">Gần vượt dự toán</span></td>
                    <td>89,000,000 đ</td>
                    <td>100,000,000 đ</td>
                    <td><a href="index.php?act=admin/soSanhDuToan&du_toan_id=2" class="btn btn-info btn-sm">Xem chi tiết</a></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Các tour có cảnh báo cần được kiểm tra lại chi phí để tránh vượt ngân sách!
    </div>
</div>
<style>tr[data-href]{cursor:pointer;}tr[data-href]:hover td{background:rgba(255,255,255,0.04)!important;}</style>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
document.querySelectorAll('tr[data-href]').forEach(function(row){
    row.addEventListener('click',function(e){
        if(e.target.closest('a,button,form,input,select,textarea')) return;
        window.location.assign(row.dataset.href);
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
