#!/usr/bin/env php
<?php
/**
 * scripts/server_tuning_check.php
 *
 * Kiểm tra cấu hình server hiện tại và đưa ra khuyến nghị
 * để hệ thống chịu được 500–1000 concurrent users.
 *
 * Chạy từ CLI:
 *   php project_quan_ly_tour_du_lich/scripts/server_tuning_check.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/commons/env.php';

$ok   = '  [OK]   ';
$warn = '  [WARN] ';
$fail = '  [FAIL] ';

echo "\n=================================================================\n";
echo "  AVENTURA — Server Tuning Check (target: 500-1000 CCU)\n";
echo "=================================================================\n\n";

// ── 1. MySQL max_connections ──────────────────────────────────────────────────
echo "── 1. MySQL max_connections ─────────────────────────────────────\n";
try {
    $conn = getPDOConnection();
    $maxConn = (int)$conn->query("SHOW VARIABLES LIKE 'max_connections'")->fetchColumn(1);
    $threadConn = (int)$conn->query("SHOW STATUS LIKE 'Threads_connected'")->fetchColumn(1);
    $innodb_lwt = (int)$conn->query("SHOW VARIABLES LIKE 'innodb_lock_wait_timeout'")->fetchColumn(1);
    $maxAllowed = (int)$conn->query("SHOW VARIABLES LIKE 'max_allowed_packet'")->fetchColumn(1);

    if ($maxConn >= 400) {
        echo $ok . "max_connections = $maxConn (khuyến nghị >= 400)\n";
    } elseif ($maxConn >= 200) {
        echo $warn . "max_connections = $maxConn — tăng lên 400+ cho 500 CCU\n";
        echo "        Fix: thêm vào my.cnf: max_connections = 500\n";
    } else {
        echo $fail . "max_connections = $maxConn — QUÁ THẤP, sẽ sập khi >= $maxConn workers\n";
        echo "        Fix: thêm vào my.cnf: max_connections = 500\n";
    }

    echo "        Threads_connected hiện tại: $threadConn\n";

    if ($innodb_lwt <= 15) {
        echo $ok . "innodb_lock_wait_timeout = {$innodb_lwt}s (hợp lý)\n";
    } else {
        echo $warn . "innodb_lock_wait_timeout = {$innodb_lwt}s — giảm xuống 10-15s để fail-fast\n";
        echo "        Fix: innodb_lock_wait_timeout = 10\n";
    }
} catch (Throwable $e) {
    echo $fail . "Không kết nối được MySQL: " . $e->getMessage() . "\n";
}

// ── 2. PHP-FPM / process config ───────────────────────────────────────────────
echo "\n── 2. PHP Configuration ─────────────────────────────────────────\n";

$maxExecTime = (int)ini_get('max_execution_time');
$memLimit    = ini_get('memory_limit');
$gcProb      = (int)ini_get('session.gc_probability');
$gcDiv       = (int)ini_get('session.gc_divisor');
$sessHandler = ini_get('session.save_handler');

echo "  max_execution_time = $maxExecTime s\n";
if ($maxExecTime > 60) {
    echo $warn . "max_execution_time quá cao — giảm xuống 30s\n";
} else {
    echo $ok . "max_execution_time hợp lý\n";
}

echo "  memory_limit       = $memLimit\n";

echo "  session.save_handler = $sessHandler\n";
if ($sessHandler === 'redis') {
    echo $ok . "Session dùng Redis — tốt cho concurrency cao\n";
} elseif ($sessHandler === 'files') {
    $gcRate = $gcDiv > 0 ? round($gcProb / $gcDiv * 100, 2) : 0;
    echo $warn . "Session dùng files — file lock tranh chấp ở 500 CCU\n";
    echo "        Fix: cài Redis + set REDIS_HOST trong .env để tự động chuyển\n";
    echo "        GC probability = $gcProb/$gcDiv ({$gcRate}%) — giảm xuống 1/1000\n";
}

// ── 3. Redis availability ─────────────────────────────────────────────────────
echo "\n── 3. Redis ─────────────────────────────────────────────────────\n";
$redisHost = defined('REDIS_HOST') ? (string)REDIS_HOST : '';
if (!extension_loaded('redis')) {
    echo $warn . "PHP redis extension chưa cài\n";
    echo "        Fix (Laragon): bật extension=redis trong php.ini\n";
} elseif ($redisHost === '') {
    echo $warn . "REDIS_HOST chưa set trong .env — chưa dùng Redis\n";
    echo "        Fix: thêm REDIS_HOST=127.0.0.1 vào .env\n";
} else {
    try {
        $r = new Redis();
        $connected = $r->connect($redisHost, defined('REDIS_PORT') ? (int)REDIS_PORT : 6379, 2.0);
        if ($connected) {
            $info = $r->info('server');
            echo $ok . "Redis kết nối OK — " . ($info['redis_version'] ?? '') . "\n";
            echo "        Session handler: " . ini_get('session.save_handler') . "\n";
            $usedMem = $r->info('memory')['used_memory_human'] ?? 'N/A';
            echo "        Memory used: $usedMem\n";
        } else {
            echo $fail . "Redis không kết nối được tới $redisHost\n";
        }
    } catch (Throwable $e) {
        echo $fail . "Redis lỗi: " . $e->getMessage() . "\n";
    }
}

// ── 4. Email queue ────────────────────────────────────────────────────────────
echo "\n── 4. Email Queue ───────────────────────────────────────────────\n";
try {
    $conn = getPDOConnection();
    $tableExists = $conn->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='email_queue'")->fetchColumn();
    if ($tableExists) {
        $pending = $conn->query("SELECT COUNT(*) FROM email_queue WHERE status='pending'")->fetchColumn();
        $failed  = $conn->query("SELECT COUNT(*) FROM email_queue WHERE status='failed'")->fetchColumn();
        echo $ok . "Bảng email_queue tồn tại — pending: $pending, failed: $failed\n";
        if ((int)$pending > 50) {
            echo $warn . "Queue có $pending emails đang chờ — kiểm tra cron job có chạy không\n";
        }
        if ((int)$failed > 0) {
            echo $warn . "$failed emails thất bại — kiểm tra cấu hình SMTP\n";
        }
    } else {
        echo $fail . "Bảng email_queue chưa tồn tại — chạy migration V017\n";
        echo "        Fix: mysql -u root -p < migrations/V017__email_queue_table.sql\n";
    }
} catch (Throwable $e) {
    echo $fail . "Lỗi kiểm tra email_queue: " . $e->getMessage() . "\n";
}

// ── 5. Apache / PHP-FPM ───────────────────────────────────────────────────────
echo "\n── 5. Hướng dẫn tuning Apache / PHP-FPM ────────────────────────\n";
echo <<<GUIDE
  Thêm vào C:\laragon\bin\apache\Apache2.x\conf\httpd.conf (hoặc apache-extra):

    # Dùng mpm_event thay prefork — xử lý concurrency tốt hơn
    # LoadModule mpm_prefork_module modules/mod_mpm_prefork.so
    LoadModule mpm_event_module modules/mod_mpm_event.so

    <IfModule mpm_event_module>
        StartServers          4
        MinSpareThreads      25
        MaxSpareThreads      75
        ThreadLimit          64
        ThreadsPerChild      25
        MaxRequestWorkers   400   # phải <= max_connections MySQL
        MaxConnectionsPerChild 0
    </IfModule>

  Hoặc nếu dùng PHP-FPM, thêm vào php-fpm pool:
    pm = dynamic
    pm.max_children      = 100
    pm.start_servers     = 10
    pm.min_spare_servers = 5
    pm.max_spare_servers = 20
    pm.max_requests      = 500   # ngăn memory leak

GUIDE;

// ── 6. MySQL my.cnf ───────────────────────────────────────────────────────────
echo "── 6. MySQL my.cnf (C:\laragon\data\mysql\my.ini) ──────────────\n";
echo <<<MYCNF
  [mysqld]
  max_connections        = 500
  innodb_lock_wait_timeout = 10
  innodb_buffer_pool_size  = 512M  # ~50% RAM nếu server dành riêng cho MySQL
  query_cache_type         = 0     # tắt query cache (deprecated, gây tranh chấp)
  thread_cache_size        = 32
  wait_timeout             = 60
  interactive_timeout      = 60

MYCNF;

echo "=================================================================\n\n";
