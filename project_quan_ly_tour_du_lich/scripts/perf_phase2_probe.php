<?php
require __DIR__ . '/../commons/env.php';
require __DIR__ . '/../commons/function.php';

$db = connectDB();

$indexTargets = [
    'booking' => [
        'idx_booking_status_ngaydat',
        'idx_booking_lich_status',
        'idx_booking_tour_ngay_ngaydat',
    ],
    'thong_bao' => ['idx_tb_role_title_status_created'],
    'giao_dich_tai_chinh' => ['idx_gd_loai_ngay'],
    'payment_logs' => ['idx_pl_action_logtime'],
    'payment_idempotency' => ['idx_pidem_status_created'],
];

$indexes = [];
$idxStmt = $db->prepare(
    'SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?'
);
foreach ($indexTargets as $table => $names) {
    $indexes[$table] = [];
    foreach ($names as $name) {
        $idxStmt->execute([$table, $name]);
        $indexes[$table][$name] = ((int)$idxStmt->fetchColumn() > 0);
    }
}

$queries = [
    'booking_pending_count' => "SELECT COUNT(*) FROM booking WHERE trang_thai = 'ChoXacNhan'",
    'finance_revenue_12m' => "SELECT COALESCE(SUM(so_tien), 0) FROM giao_dich_tai_chinh WHERE loai = 'Thu' AND ngay_giao_dich >= DATE_SUB(NOW(), INTERVAL 12 MONTH)",
    'thong_bao_admin_sla' => "SELECT COUNT(*) FROM thong_bao WHERE vai_tro_nhan = 'Admin' AND tieu_de = 'Yêu cầu tour theo mong muốn' AND trang_thai = 'DaGui' AND created_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR)",
    'payment_logs_warn_24h' => "SELECT COUNT(*) FROM payment_logs WHERE action IN ('AUTO_RECONCILE_WARN', 'STATE_TRANSITION_BLOCKED') AND log_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)",
    'payment_idempotency_failed_24h' => "SELECT COUNT(*) FROM payment_idempotency WHERE status = 'failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)",
];

$explain = [];
foreach ($queries as $name => $sql) {
    $rows = $db->query('EXPLAIN ' . $sql)->fetchAll(PDO::FETCH_ASSOC);
    $explain[$name] = array_map(static function ($r) {
        return [
            'table' => $r['table'] ?? null,
            'type' => $r['type'] ?? null,
            'possible_keys' => $r['possible_keys'] ?? null,
            'key' => $r['key'] ?? null,
            'rows' => isset($r['rows']) ? (int)$r['rows'] : null,
            'extra' => $r['Extra'] ?? null,
        ];
    }, $rows);
}

echo json_encode([
    'indexes' => $indexes,
    'explain' => $explain,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
