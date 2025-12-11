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
    </style>

<div style="padding: 20px; max-width: 900px; margin: 0 auto;">
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
                <tr>
                    <td>1</td>
                    <td>Tour Đà Nẵng 3N2Đ</td>
                    <td><span class="badge bg-danger">Vượt dự toán</span></td>
                    <td>120,000,000 đ</td>
                    <td>100,000,000 đ</td>
                    <td><a href="index.php?act=admin/soSanhDuToan&du_toan_id=1" class="btn btn-info btn-sm">Xem chi tiết</a></td>
                </tr>
                <tr>
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
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
