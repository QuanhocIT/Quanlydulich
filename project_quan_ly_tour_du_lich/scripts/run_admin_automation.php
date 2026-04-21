<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../commons/function.php';
require_once __DIR__ . '/../services/AdminAutomationService.php';

$job = $argv[1] ?? 'all';
$job = trim((string)$job);

$availableJobs = [
    'all',
    'sla_tour_requests',
    'booking_priority',
    'reconcile_digest',
    'self_heal_pending_payments',
    'webhook_anomaly',
    'debt_reminder',
    'departure_readiness',
    'tour_health_score',
    'admin_inbox_digest',
    'decision_assist',
];

if (!in_array($job, $availableJobs, true)) {
    fwrite(STDERR, "Unknown job: {$job}\n");
    fwrite(STDERR, "Usage: php scripts/run_admin_automation.php [all|" . implode('|', array_slice($availableJobs, 1)) . "]\n");
    exit(1);
}

$conn = getPDOConnection();
$service = new AdminAutomationService($conn);

if (!$service->isAutomationEnabled()) {
    echo "[SKIPPED] automation is disabled by admin switch.\n";
    exit(0);
}

$startedAt = microtime(true);

if ($job === 'all') {
    $results = $service->runAll();
} else {
    $results = [$service->runJob($job)];
}

$totalAffected = 0;
$failed = 0;
foreach ($results as $result) {
    $ok = !empty($result['ok']);
    $jobName = (string)($result['job'] ?? 'unknown');
    $affected = (int)($result['affected'] ?? 0);
    $durationMs = (float)($result['duration_ms'] ?? 0.0);
    $message = (string)($result['message'] ?? '');

    if (!$ok) {
        $failed++;
    }

    $totalAffected += max(0, $affected);

    echo sprintf(
        "[%s] job=%s affected=%d duration_ms=%.1f message=%s\n",
        $ok ? 'OK' : 'ERROR',
        $jobName,
        $affected,
        $durationMs,
        $message
    );
}

$totalMs = round((microtime(true) - $startedAt) * 1000, 1);
echo "----------------------------------------\n";
echo "Total jobs: " . count($results) . "\n";
echo "Failed jobs: " . $failed . "\n";
echo "Total affected: " . $totalAffected . "\n";
echo "Elapsed(ms): " . $totalMs . "\n";

exit($failed > 0 ? 2 : 0);
