#!/usr/bin/env php
<?php
/**
 * Cron job xử lý email_queue.
 *
 * Thêm vào Task Scheduler (Windows) hoặc crontab (Linux) chạy mỗi 1 phút:
 *
 *   Windows Task Scheduler:
 *     Program: C:\laragon\bin\php\php8.x\php.exe
 *     Arguments: C:\laragon\www\Quanlydulich-main\project_quan_ly_tour_du_lich\scripts\process_email_queue.php
 *     Trigger: Every 1 minute
 *
 *   Linux crontab:
 *     * * * * * php /path/to/project_quan_ly_tour_du_lich/scripts/process_email_queue.php >> /var/log/email_queue.log 2>&1
 */

// Chỉ cho phép chạy từ CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

// Ngăn chạy nhiều instance song song bằng file lock
$lockFile = sys_get_temp_dir() . '/aventura_email_queue.lock';
$lock = fopen($lockFile, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    echo "[" . date('Y-m-d H:i:s') . "] Another instance is running. Skipping.\n";
    exit(0);
}

$projectRoot = dirname(__DIR__);

// Load cấu hình .env / constants
require_once $projectRoot . '/commons/env.php';
require_once $projectRoot . '/commons/function.php';
require_once $projectRoot . '/services/EmailQueueService.php';

$start = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Processing email queue...\n";

$result = EmailQueueService::processQueue(20);

$elapsed = round(microtime(true) - $start, 3);
echo "[" . date('Y-m-d H:i:s') . "] Done: sent={$result['sent']}, failed={$result['failed']}, time={$elapsed}s\n";

flock($lock, LOCK_UN);
fclose($lock);
exit(0);
