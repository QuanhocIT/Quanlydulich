<?php

// Apply performance indexes from storage/migrate_performance_indexes.sql
// Usage: php scripts/apply_performance_indexes.php

require_once __DIR__ . '/../commons/env.php';

$sqlFile = __DIR__ . '/../storage/migrate_performance_indexes.sql';
if (!is_file($sqlFile)) {
    fwrite(STDERR, "Missing SQL file: {$sqlFile}\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "SQL file empty/unreadable: {$sqlFile}\n");
    exit(1);
}

// Naive splitter; sufficient for typical CREATE INDEX statements (no stored procedures here).
$statements = preg_split('/;\s*(\r?\n|$)/', $sql) ?: [];
$statements = array_values(array_filter(array_map('trim', $statements), function ($s) {
    return $s !== '' && strpos($s, '--') !== 0;
}));

$conn = getPDOConnection();

$applied = 0;
$skipped = 0;
$failed = 0;

foreach ($statements as $statement) {
    try {
        $conn->exec($statement);
        $applied++;
    } catch (PDOException $e) {
        // Duplicate key name / index exists (MySQL error 1061) -> skip
        $code = (int)($e->errorInfo[1] ?? 0);
        if ($code === 1061) {
            $skipped++;
            continue;
        }
        $failed++;
        fwrite(STDERR, "FAILED: {$statement}\n");
        fwrite(STDERR, "  Error: " . $e->getMessage() . "\n");
    }
}

echo "Done.\n";
echo "Applied: {$applied}\n";
echo "Skipped (exists): {$skipped}\n";
echo "Failed: {$failed}\n";

exit($failed > 0 ? 2 : 0);

