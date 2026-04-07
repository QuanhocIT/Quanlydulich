<?php
/**
 * Script migration: Băm tất cả mật khẩu plaintext trong bảng nguoi_dung
 * Chạy một lần để nâng cấp bảo mật từ plaintext → bcrypt hash
 * 
 * Cách chạy: php scripts/hash_passwords.php
 */

require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../commons/function.php';

$conn = connectDB();

if (!$conn) {
    echo "❌ Kết nối database thất bại!\n";
    exit(1);
}

// Lấy tất cả người dùng hiện tại
$sql = "SELECT id, mat_khau FROM nguoi_dung WHERE mat_khau IS NOT NULL AND mat_khau != ''";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "✅ Không có người dùng nào cần cập nhật.\n";
    exit(0);
}

$updated = 0;
$skipped = 0;

foreach ($users as $user) {
    $id = $user['id'];
    $mat_khau = $user['mat_khau'];

    // Kiểm tra xem mật khẩu đã là hash (bcrypt) hay còn plaintext
    // Hash bcrypt luôn bắt đầu bằng $2a$, $2b$, hoặc $2y$
    if (password_needs_rehash($mat_khau, PASSWORD_DEFAULT)) {
        // Mật khẩu cần được băm lại (plaintext hoặc hash cũ)
        $hash = password_hash($mat_khau, PASSWORD_DEFAULT);
        
        // Cập nhật vào database
        $updateSql = "UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        
        if ($updateStmt->execute([$hash, $id])) {
            $updated++;
            echo "✅ ID $id: Đã băm mật khẩu.\n";
        } else {
            echo "❌ ID $id: Cập nhật thất bại.\n";
        }
    } else {
        // Mật khẩu đã là hash an toàn
        $skipped++;
        echo "⏭️  ID $id: Mật khẩu đã an toàn (bỏ qua).\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 Kết quả migration:\n";
echo "   • Cập nhật: $updated người dùng\n";
echo "   • Bỏ qua: $skipped người dùng\n";
echo "   • Tổng: " . count($users) . " người dùng kiểm tra\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Migration hoàn tất!\n";
?>
