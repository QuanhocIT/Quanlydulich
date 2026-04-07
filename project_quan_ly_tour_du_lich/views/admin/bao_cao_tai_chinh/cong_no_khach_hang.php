<?php
// $congNoKhachHang được truyền từ controller
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công nợ khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh;}
        .main-card {max-width: 80%; margin: 40px auto; background: #dcd4d4ff; border-radius: 18px; box-shadow: 0 8px 32px rgba(120,120,180,0.12); padding: 32px;}
        .table th, .table td {vertical-align: middle;}
        .table th {background: #9c99a3ff; color: #fff;}
        .table td {background: #f8fafc;}
        .fw-bold {font-weight: bold;}
    </style>
</head>
<body>
    <div class="main-card">
        <h2 class="mb-4" style="color:#6a4bc6;"><i class="fas fa-user-friends"></i> Công nợ khách hàng (chỉ cọc)</h2>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Tour</th>
                        <th>Số tiền nợ</th>
                        <th>Ngày đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($congNoKhachHang)) {
                        echo '<tr><td colspan="6" class="text-center text-muted">Không có công nợ nào</td></tr>';
                    } else {
                        foreach ($congNoKhachHang as $row) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['ten_khach_hang'] ?? 'N/A') . '</td>';
                            echo '<td>' . htmlspecialchars($row['email'] ?? '') . '</td>';
                            echo '<td>' . htmlspecialchars($row['so_dien_thoai'] ?? '') . '</td>';
                            echo '<td>' . htmlspecialchars($row['ten_tour']) . '</td>';
                            echo '<td class="text-danger fw-bold">' . number_format($row['cong_no']) . 'đ</td>';
                            echo '<td>';
                            if (!empty($row['lich_su_thanh_toan'])) {
                                foreach ($row['lich_su_thanh_toan'] as $ls) {
                                    echo '<div style="font-size:13px;color:#6a4bc6">' . date('d/m/Y', strtotime($ls['ngay'])) . ': ' . number_format($ls['so_tien']) . 'đ</div>';
                                }
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
