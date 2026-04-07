<?php
require __DIR__ . '/../commons/env.php';

$conn = getPDOConnection();
$sql = "SELECT log_id, payment_id, action, log_time, note
        FROM payment_logs
        WHERE action LIKE 'WEBHOOK%'
        ORDER BY log_id DESC
        LIMIT 20";

$stmt = $conn->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
