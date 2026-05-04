#!/usr/bin/env php
<?php
/**
 * scripts/backup_db.php
 *
 * Backup MySQL database bằng mysqldump.
 * Lưu file nén (.sql.gz) vào thư mục cấu hình bên dưới.
 * Tự động xóa backup cũ hơn RETENTION_DAYS ngày.
 *
 * Cách chạy thủ công:
 *   php scripts/backup_db.php
 *
 * Cách lên lịch Windows Task Scheduler (hàng ngày lúc 2:00 sáng):
 *   Program : C:\laragon\bin\php\php-8.x\php.exe
 *   Arguments: C:\laragon\www\Quanlydulich-main\project_quan_ly_tour_du_lich\scripts\backup_db.php
 *
 * Cách lên lịch Linux/Mac cron (hàng ngày lúc 2:00 sáng):
 *   0 2 * * * /usr/bin/php /var/www/html/scripts/backup_db.php >> /var/log/db_backup.log 2>&1
 */

// ── Cấu hình ──────────────────────────────────────────────────────────────
require_once __DIR__ . '/../commons/env.php';

define('BACKUP_DIR',       __DIR__ . '/../storage/backups');
define('RETENTION_DAYS',   14);          // Giữ backup trong 14 ngày
define('MYSQLDUMP_BIN',    detectMysqldump()); // Tự tìm mysqldump

// ── Helper: tìm mysqldump ─────────────────────────────────────────────────
function detectMysqldump(): string {
    $candidates = [
        'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe',
        'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
        '/usr/bin/mysqldump',
        '/usr/local/bin/mysqldump',
        'mysqldump', // từ PATH
    ];
    foreach ($candidates as $path) {
        if (is_executable($path) || ($path === 'mysqldump')) {
            return $path;
        }
    }
    return 'mysqldump';
}

// ── Main ──────────────────────────────────────────────────────────────────
$host   = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
$port   = defined('DB_PORT') ? (string)DB_PORT : '3306';
$dbName = defined('DB_NAME') ? DB_NAME : 'quan_ly_tour_du_lich';
$user   = defined('DB_USER') ? DB_USER : 'root';
$pass   = defined('DB_PASS') ? DB_PASS : '';

if (!is_dir(BACKUP_DIR)) {
    if (!mkdir(BACKUP_DIR, 0750, true)) {
        fwrite(STDERR, "[BACKUP] ERROR: Không thể tạo thư mục " . BACKUP_DIR . "\n");
        exit(1);
    }
}

$timestamp  = date('Y-m-d_H-i-s');
$filename   = $dbName . '_' . $timestamp . '.sql.gz';
$targetPath = BACKUP_DIR . DIRECTORY_SEPARATOR . $filename;

// Xây lệnh mysqldump — truyền password qua env var để tránh lộ trong process list
$passPart   = $pass !== '' ? ' -p' . escapeshellarg($pass) : '';
$portPart   = $port !== '3306' ? ' --port=' . (int)$port : '';

$cmd = sprintf(
    '%s --host=%s%s --user=%s%s --single-transaction --routines --triggers --set-gtid-purged=OFF %s | gzip > %s',
    escapeshellcmd(MYSQLDUMP_BIN),
    escapeshellarg($host),
    $portPart,
    escapeshellarg($user),
    $passPart,
    escapeshellarg($dbName),
    escapeshellarg($targetPath)
);

echo "[BACKUP] " . date('c') . " — Bắt đầu backup $dbName ...\n";
$output = [];
$exitCode = 0;
exec($cmd . ' 2>&1', $output, $exitCode);

if ($exitCode !== 0 || !file_exists($targetPath) || filesize($targetPath) < 100) {
    fwrite(STDERR, "[BACKUP] ERROR (exit=$exitCode): " . implode("\n", $output) . "\n");
    // Xóa file rỗng / lỗi nếu đã tạo
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    exit(1);
}

$sizeMb = round(filesize($targetPath) / 1048576, 2);
echo "[BACKUP] OK — $filename ($sizeMb MB)\n";

// ── Xóa backup cũ ────────────────────────────────────────────────────────
$cutoff  = time() - RETENTION_DAYS * 86400;
$deleted = 0;
foreach (glob(BACKUP_DIR . DIRECTORY_SEPARATOR . '*.sql.gz') ?: [] as $f) {
    if (filemtime($f) < $cutoff) {
        unlink($f);
        $deleted++;
    }
}
if ($deleted > 0) {
    echo "[BACKUP] Đã xóa $deleted file backup cũ hơn " . RETENTION_DAYS . " ngày.\n";
}

echo "[BACKUP] Hoàn tất.\n";
exit(0);
