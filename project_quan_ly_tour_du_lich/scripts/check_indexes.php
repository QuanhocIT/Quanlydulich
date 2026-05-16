<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'quan_ly_tour_du_lich';

try {
    $pdo = new PDO('mysql:host='.$host.';dbname='.$db, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== INDEXES TRONG DATABASE ===\n\n";
    
    // Kiểm tra index trong booking table
    $stmt = $pdo->query("SHOW INDEXES FROM booking");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($results) {
        echo "📍 TABLE: booking\n";
        foreach ($results as $row) {
            echo "  - Index: {$row['Key_name']} on column(s): {$row['Column_name']}\n";
        }
    }
    
    echo "\n";
    
    // Kiểm tra index trong lich_khoi_hanh table
    $stmt = $pdo->query("SHOW INDEXES FROM lich_khoi_hanh");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($results) {
        echo "📍 TABLE: lich_khoi_hanh\n";
        foreach ($results as $row) {
            echo "  - Index: {$row['Key_name']} on column(s): {$row['Column_name']}\n";
        }
    }
    
    echo "\n";
    
    // Kiểm tra index trong giao_dich_tai_chinh table
    $stmt = $pdo->query("SHOW INDEXES FROM giao_dich_tai_chinh");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($results) {
        echo "📍 TABLE: giao_dich_tai_chinh\n";
        foreach ($results as $row) {
            echo "  - Index: {$row['Key_name']} on column(s): {$row['Column_name']}\n";
        }
    }
    
    echo "\n";
    
    // Kiểm tra index trong payments table nếu tồn tại
    try {
        $stmt = $pdo->query("SHOW INDEXES FROM payments");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($results) {
            echo "📍 TABLE: payments\n";
            foreach ($results as $row) {
                echo "  - Index: {$row['Key_name']} on column(s): {$row['Column_name']}\n";
            }
        }
    } catch (Exception $e) {
        echo "⚠️ TABLE: payments (không tồn tại hoặc không thể truy cập)\n";
    }
    
} catch (Exception $e) {
    echo 'Lỗi kết nối: ' . $e->getMessage();
}
?>
