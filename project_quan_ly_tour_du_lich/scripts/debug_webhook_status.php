<?php
require __DIR__ . '/../commons/env.php';

$conn = getPDOConnection();

echo "=== WEBHOOK STATUS ===\n";

echo 'BANK_WEBHOOK_ENABLED=' . (BANK_WEBHOOK_ENABLED ? '1' : '0') . "\n";
echo 'BANK_WEBHOOK_PROVIDER=' . BANK_WEBHOOK_PROVIDER . "\n";
echo 'BANK_WEBHOOK_SECRET_SET=' . (trim((string)BANK_WEBHOOK_SECRET) !== '' ? '1' : '0') . "\n";

$webhookCount = (int)$conn->query("SELECT COUNT(*) FROM payment_logs WHERE action LIKE 'WEBHOOK_%'")->fetchColumn();
echo 'WEBHOOK_LOG_COUNT=' . $webhookCount . "\n";

$paymentRows = $conn->query("SELECT payment_id, booking_id, amount, status, payment_method, payment_date FROM payments ORDER BY payment_id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== LATEST PAYMENTS ===\n";
foreach ($paymentRows as $r) {
    echo sprintf(
        "payment_id=%d booking_id=%d amount=%s status=%s method=%s date=%s\n",
        (int)$r['payment_id'],
        (int)$r['booking_id'],
        (string)$r['amount'],
        (string)$r['status'],
        (string)$r['payment_method'],
        (string)$r['payment_date']
    );
}

$rows = $conn->query("SELECT log_id,payment_id,action,log_time,note FROM payment_logs ORDER BY log_id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== LATEST PAYMENT LOGS ===\n";
foreach ($rows as $r) {
    echo sprintf(
        "log_id=%d payment_id=%d action=%s time=%s note=%s\n",
        (int)$r['log_id'],
        (int)$r['payment_id'],
        (string)$r['action'],
        (string)$r['log_time'],
        str_replace(["\r", "\n"], ' ', (string)$r['note'])
    );
}
