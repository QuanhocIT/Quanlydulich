<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

require_once __DIR__ . '/../commons/env.php';

const MIGRATION_TABLE = 'schema_migrations';
const MIGRATIONS_DIR = __DIR__ . '/../migrations';

$command = $argv[1] ?? 'status';
$stepArg = 0;
foreach ($argv as $arg) {
    if (strpos($arg, '--step=') === 0) {
        $stepArg = max(0, (int)substr($arg, 7));
    }
}

$pdo = getPDOConnection();
ensureMigrationTable($pdo);

switch ($command) {
    case 'status':
        showStatus($pdo);
        break;

    case 'up':
        applyPendingMigrations($pdo, $stepArg);
        break;

    default:
        fwrite(STDERR, "Unknown command: {$command}\n");
        fwrite(STDERR, "Usage: php scripts/migrate.php [status|up] [--step=N]\n");
        exit(1);
}

function ensureMigrationTable(PDO $pdo): void
{
    $sql = "CREATE TABLE IF NOT EXISTS " . MIGRATION_TABLE . " (
        version VARCHAR(32) NOT NULL,
        name VARCHAR(255) NOT NULL,
        checksum CHAR(64) NOT NULL,
        executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (version)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
}

function showStatus(PDO $pdo): void
{
    $migrations = listMigrationFiles();
    $applied = getAppliedMigrations($pdo);

    echo "Migration directory: " . MIGRATIONS_DIR . PHP_EOL;
    echo str_repeat('-', 80) . PHP_EOL;
    echo str_pad('Version', 12)
        . str_pad('Status', 12)
        . str_pad('Executed At', 22)
        . "Name" . PHP_EOL;
    echo str_repeat('-', 80) . PHP_EOL;

    $pendingCount = 0;
    foreach ($migrations as $migration) {
        $version = $migration['version'];
        $row = $applied[$version] ?? null;
        $status = $row ? 'APPLIED' : 'PENDING';
        $executedAt = $row['executed_at'] ?? '-';

        if (!$row) {
            $pendingCount++;
        }

        echo str_pad($version, 12)
            . str_pad($status, 12)
            . str_pad($executedAt, 22)
            . $migration['name'] . PHP_EOL;
    }

    echo str_repeat('-', 80) . PHP_EOL;
    echo 'Total: ' . count($migrations)
        . ' | Applied: ' . count($applied)
        . ' | Pending: ' . $pendingCount . PHP_EOL;
}

function applyPendingMigrations(PDO $pdo, int $limit = 0): void
{
    $migrations = listMigrationFiles();
    $applied = getAppliedMigrations($pdo);

    $pending = array_values(array_filter($migrations, static function (array $migration) use ($applied): bool {
        return !isset($applied[$migration['version']]);
    }));

    if ($limit > 0) {
        $pending = array_slice($pending, 0, $limit);
    }

    if (empty($pending)) {
        echo "No pending migrations.\n";
        return;
    }

    foreach ($pending as $migration) {
        echo 'Applying ' . $migration['version'] . ' - ' . $migration['name'] . ' ... '; 
        runMigrationFile($pdo, $migration['path']);
        recordMigration($pdo, $migration);
        echo "DONE\n";
    }

    echo 'Applied ' . count($pending) . " migration(s).\n";
}

function listMigrationFiles(): array
{
    if (!is_dir(MIGRATIONS_DIR)) {
        throw new RuntimeException('Migration directory not found: ' . MIGRATIONS_DIR);
    }

    $files = glob(MIGRATIONS_DIR . '/V*.sql');
    if (!is_array($files)) {
        return [];
    }

    $result = [];
    foreach ($files as $filePath) {
        $baseName = basename($filePath);
        if (!preg_match('/^(V\d{3,})__(.+)\.sql$/', $baseName, $matches)) {
            continue;
        }

        $version = $matches[1];
        $name = str_replace('_', ' ', $matches[2]);
        $checksum = hash_file('sha256', $filePath) ?: '';

        $result[] = [
            'version' => $version,
            'name' => $name,
            'path' => $filePath,
            'checksum' => $checksum,
        ];
    }

    usort($result, static function (array $left, array $right): int {
        return strcmp($left['version'], $right['version']);
    });

    return $result;
}

function getAppliedMigrations(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT version, name, checksum, executed_at FROM ' . MIGRATION_TABLE . ' ORDER BY version ASC');
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $result = [];
    foreach ($rows as $row) {
        $result[(string)$row['version']] = $row;
    }

    return $result;
}

function runMigrationFile(PDO $pdo, string $filePath): void
{
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        throw new RuntimeException('Cannot read migration file: ' . $filePath);
    }

    $statements = splitSqlStatements($sql);
    foreach ($statements as $statement) {
        $trimmed = trim($statement);
        if ($trimmed === '') {
            continue;
        }

        runSqlStatement($pdo, $trimmed);
    }
}

function splitSqlStatements(string $sql): array
{
    $lines = preg_split('/\R/', $sql) ?: [];
    $statements = [];
    $buffer = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
            continue;
        }

        $buffer .= $line . "\n";

        if (preg_match('/;\s*$/', $trimmed)) {
            $statements[] = $buffer;
            $buffer = '';
        }
    }

    if (trim($buffer) !== '') {
        $statements[] = $buffer;
    }

    return $statements;
}

function runSqlStatement(PDO $pdo, string $statement): void
{
    $firstToken = strtoupper((string)strtok(ltrim($statement), " \n\r\t("));
    $isQuery = in_array($firstToken, ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN', 'EXECUTE'], true);

    if ($isQuery) {
        $query = $pdo->query($statement);
        if ($query === false) {
            throwPdoError($pdo, $statement);
        }
        $query->fetchAll(PDO::FETCH_ASSOC);
        $query->closeCursor();
        return;
    }

    $ok = $pdo->exec($statement);
    if ($ok === false) {
        throwPdoError($pdo, $statement);
    }
}

function throwPdoError(PDO $pdo, string $statement): void
{
    $error = $pdo->errorInfo();
    $message = isset($error[2]) ? (string)$error[2] : 'Unknown SQL error';
    throw new RuntimeException('SQL failed: ' . $message . ' | Statement: ' . $statement);
}

function recordMigration(PDO $pdo, array $migration): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO ' . MIGRATION_TABLE . ' (version, name, checksum, executed_at) VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([
        (string)$migration['version'],
        (string)$migration['name'],
        (string)$migration['checksum'],
    ]);
}
