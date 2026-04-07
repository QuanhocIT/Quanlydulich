<?php

// Lightweight request + DB profiling (opt-in via .env)
// - PERF_HEADER_ENABLED=1: adds X-Perf-* headers
// - PERF_LOG_ENABLED=1: logs slow requests to storage/perf.log
// - PERF_DB_PROFILE=1: enables PDO statement profiling (see commons/env.php)

if (!defined('PERF_HEADER_ENABLED')) {
    return;
}

$__perfStart = microtime(true);

register_shutdown_function(function () use ($__perfStart) {
    $durationMs = (microtime(true) - $__perfStart) * 1000.0;
    $memoryPeak = memory_get_peak_usage(true);

    $dbQueries = null;
    $dbMs = null;
    $slowQueries = null;
    if (class_exists('ProfiledPDOStatement', false)) {
        $dbQueries = ProfiledPDOStatement::$queryCount;
        $dbMs = ProfiledPDOStatement::$queryTimeMs;
        $slowQueries = ProfiledPDOStatement::$slowQueries;
    }

    if (defined('PERF_HEADER_ENABLED') && PERF_HEADER_ENABLED && !headers_sent()) {
        header('X-Perf-Time-Ms: ' . (string)round($durationMs, 1));
        if ($dbMs !== null) {
            header('X-Perf-Db-Ms: ' . (string)round((float)$dbMs, 1));
        }
        if ($dbQueries !== null) {
            header('X-Perf-Db-Queries: ' . (string)(int)$dbQueries);
        }
    }

    if (!defined('PERF_LOG_ENABLED') || !PERF_LOG_ENABLED) {
        return;
    }

    $slowMs = defined('PERF_SLOW_MS') ? (int)PERF_SLOW_MS : 800;
    if ($durationMs < $slowMs) {
        return;
    }

    $payload = [
        'ts' => date('c'),
        'ms' => round($durationMs, 1),
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'act' => isset($_GET['act']) ? (string)$_GET['act'] : '',
        'db_ms' => $dbMs !== null ? round((float)$dbMs, 1) : null,
        'db_q' => $dbQueries !== null ? (int)$dbQueries : null,
        'mem_peak' => $memoryPeak,
        'slow_sql' => $slowQueries ?: null,
    ];

    $logDir = __DIR__ . '/../storage';
    $logFile = $logDir . '/perf.log';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    @file_put_contents($logFile, json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
});

