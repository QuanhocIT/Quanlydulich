<?php

declare(strict_types=1);

require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../commons/function.php';

$pdo = connectDB();

echo "=================================================================\n";
echo "            COMPREHENSIVE SYSTEM HEALTH & ERROR AUDIT            \n";
echo "=================================================================\n\n";

$issues = [];

// ===================================================================
// 1. ROUTE AUDIT: Parse index.php and check all Controller::method calls
// ===================================================================
echo "[1] Checking all routes and controller actions in index.php...\n";
$indexContent = file_get_contents(__DIR__ . '/../index.php');

// Match route mappings: 'act_name' => (new ControllerName())->methodName(),
preg_match_all("/'([^']+)'\s*=>\s*\(new\s+([A-Za-z0-9_]+)\s*\(\)\)->([A-Za-z0-9_]+)\s*\(/", $indexContent, $matches, PREG_SET_ORDER);

$checkedControllers = [];
$routeIssues = 0;

foreach ($matches as $m) {
    $act = $m[1];
    $controllerClass = $m[2];
    $methodName = $m[3];

    $controllerFile = __DIR__ . '/../controllers/' . $controllerClass . '.php';
    if (!file_exists($controllerFile)) {
        $issues[] = "[Route Error] Route '$act' points to non-existent controller file: controllers/$controllerClass.php";
        $routeIssues++;
        continue;
    }

    if (!isset($checkedControllers[$controllerClass])) {
        require_once $controllerFile;
        $checkedControllers[$controllerClass] = new ReflectionClass($controllerClass);
    }

    $ref = $checkedControllers[$controllerClass];
    if (!$ref->hasMethod($methodName)) {
        $issues[] = "[Route Error] Route '$act' calls non-existent method $controllerClass::$methodName()";
        $routeIssues++;
    } elseif (!$ref->getMethod($methodName)->isPublic()) {
        $issues[] = "[Route Error] Route '$act' calls non-public method $controllerClass::$methodName()";
        $routeIssues++;
    }
}
echo "    Checked " . count($matches) . " routes across " . count($checkedControllers) . " controllers ($routeIssues issues found).\n";

// ===================================================================
// 2. VIEW AUDIT: Check all 'views/...' referenced in controllers
// ===================================================================
echo "[2] Checking all view templates referenced in controllers...\n";
$controllerFiles = glob(__DIR__ . '/../controllers/*.php');
$viewIssues = 0;
$totalViewsChecked = 0;

foreach ($controllerFiles as $cFile) {
    $content = file_get_contents($cFile);
    preg_match_all("/(?:require|include)(?:_once)?\s*[\(\s]*['\"](views\/[^'\"]+)['\"]/", $content, $vMatches);
    foreach ($vMatches[1] as $vPath) {
        $fullPath = __DIR__ . '/../' . $vPath;
        $totalViewsChecked++;
        if (!file_exists($fullPath)) {
            $issues[] = "[View Error] Missing view file '$vPath' referenced in " . basename($cFile);
            $viewIssues++;
        }
    }
}
echo "    Checked $totalViewsChecked view inclusions ($viewIssues issues found).\n";

// ===================================================================
// 3. DATABASE INTEGRITY & ORPHANED FOREIGN KEYS CHECK
// ===================================================================
echo "[3] Checking database foreign key constraints & orphaned records...\n";
$dbIssues = 0;

// Check bookings with non-existent tour_id
$orphanBookingsTour = $pdo->query("SELECT COUNT(*) FROM booking b LEFT JOIN tour t ON b.tour_id = t.tour_id WHERE b.tour_id IS NOT NULL AND t.tour_id IS NULL")->fetchColumn();
if ($orphanBookingsTour > 0) {
    $issues[] = "[DB Integrity] Found $orphanBookingsTour bookings pointing to non-existent tour_id";
    $dbIssues++;
}

// Check bookings with non-existent khach_hang_id
$orphanBookingsKhach = $pdo->query("SELECT COUNT(*) FROM booking b LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id WHERE b.khach_hang_id IS NOT NULL AND kh.khach_hang_id IS NULL")->fetchColumn();
if ($orphanBookingsKhach > 0) {
    $issues[] = "[DB Integrity] Found $orphanBookingsKhach bookings pointing to non-existent khach_hang_id";
    $dbIssues++;
}

// Check schedules with non-existent tour_id
$orphanSchedules = $pdo->query("SELECT COUNT(*) FROM lich_khoi_hanh lk LEFT JOIN tour t ON lk.tour_id = t.tour_id WHERE lk.tour_id IS NOT NULL AND t.tour_id IS NULL")->fetchColumn();
if ($orphanSchedules > 0) {
    $issues[] = "[DB Integrity] Found $orphanSchedules schedules pointing to non-existent tour_id";
    $dbIssues++;
}

// Check staff allocations with non-existent schedule or staff
$orphanAllocStaff = $pdo->query("SELECT COUNT(*) FROM phan_bo_nhan_su pbn LEFT JOIN lich_khoi_hanh lk ON pbn.lich_khoi_hanh_id = lk.id WHERE lk.id IS NULL")->fetchColumn();
if ($orphanAllocStaff > 0) {
    $issues[] = "[DB Integrity] Found $orphanAllocStaff staff allocations pointing to non-existent schedule";
    $dbIssues++;
}

// Check service allocations with non-existent schedule
$orphanAllocService = $pdo->query("SELECT COUNT(*) FROM phan_bo_dich_vu pbdv LEFT JOIN lich_khoi_hanh lk ON pbdv.lich_khoi_hanh_id = lk.id WHERE lk.id IS NULL")->fetchColumn();
if ($orphanAllocService > 0) {
    $issues[] = "[DB Integrity] Found $orphanAllocService service allocations pointing to non-existent schedule";
    $dbIssues++;
}

echo "    Database orphan checks completed ($dbIssues issues found).\n";

// ===================================================================
// 4. MODEL TABLE NAME VERIFICATION
// ===================================================================
echo "[4] Checking table names referenced in all model SQL queries...\n";
$modelFiles = glob(__DIR__ . '/../models/*.php');
$tableIssues = 0;
$dbTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$dbTableMap = array_fill_keys(array_map('strtolower', $dbTables), true);

foreach ($modelFiles as $mFile) {
    $content = file_get_contents($mFile);
    // Remove comments to avoid false positives (e.g. "// join với ...")
    $cleanContent = preg_replace('!/\*.*?\*/!s', '', $content);
    $cleanContent = preg_replace('!//.*?$!m', '', $cleanContent);

    // Find table names in FROM or JOIN or INTO or UPDATE (avoiding PHP methods and ON DUPLICATE KEY UPDATE)
    preg_match_all('/\b(?<!KEY\s)(?:FROM|JOIN|INTO|UPDATE)\s+`?([a-zA-Z0-9_]+)`?/i', $cleanContent, $tMatches);
    foreach ($tMatches[1] as $tName) {
        $tLower = strtolower($tName);
        // Exclude SQL keywords or subqueries
        if (in_array($tLower, ['select', 'where', 'set', 'dual', 'values', 'information_schema'], true)) {
            continue;
        }
        if (!isset($dbTableMap[$tLower])) {
            $issues[] = "[Model Error] Model " . basename($mFile) . " references non-existent table '$tName'";
            $tableIssues++;
        }
    }
}
echo "    Checked " . count($modelFiles) . " models for table existence ($tableIssues issues found).\n";

// ===================================================================
// 5. VUE ADMIN BUNDLE & ASSET INTEGRITY
// ===================================================================
echo "[5] Checking Vue Admin build bundles in public/dist/admin/...\n";
$distFiles = [
    'booking-manage.js',
    'schedule-manage.js',
    'review-manage.js',
    'tour-list.js',
];
$assetIssues = 0;
foreach ($distFiles as $df) {
    $full = __DIR__ . '/../public/dist/admin/' . $df;
    if (!file_exists($full)) {
        $issues[] = "[Frontend Error] Missing compiled Vue admin bundle: public/dist/admin/$df";
        $assetIssues++;
    }
}
echo "    Vue bundles checked ($assetIssues issues found).\n";

// ===================================================================
// SUMMARY
// ===================================================================
echo "\n=================================================================\n";
if (empty($issues)) {
    echo "  EXCELLENT: 0 ISSUES FOUND! The system is healthy and consistent.\n";
} else {
    echo "  FOUND " . count($issues) . " ISSUE(S) THAT REQUIRE ATTENTION:\n";
    foreach ($issues as $idx => $iss) {
        echo "  " . ($idx + 1) . ". $iss\n";
    }
}
echo "=================================================================\n";
