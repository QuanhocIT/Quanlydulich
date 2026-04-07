<?php

// Kết nối CSDL qua PDO
function connectDB() {
    return getPDOConnection();
}

function dbColumnExists($tableName, $columnName, $conn = null) {
    static $columnCache = [];

    $tableName = trim((string)$tableName);
    $columnName = trim((string)$columnName);
    if ($tableName === '' || $columnName === '') {
        return false;
    }

    $cacheKey = strtolower($tableName . '.' . $columnName);
    if (array_key_exists($cacheKey, $columnCache)) {
        return $columnCache[$cacheKey];
    }

    $pdo = $conn instanceof PDO ? $conn : connectDB();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?"
    );
    $stmt->execute([$tableName, $columnName]);
    $columnCache[$cacheKey] = ((int)$stmt->fetchColumn() > 0);

    return $columnCache[$cacheKey];
}

function ensureAdminNotificationStateTable($conn = null) {
    static $initialized = false;

    $pdo = $conn instanceof PDO ? $conn : connectDB();
    if ($initialized && $conn === null) {
        return true;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS admin_notification_state (
            user_id INT(11) NOT NULL,
            payments_last_seen_id INT(11) NOT NULL DEFAULT 0,
            reviews_last_seen_id INT(11) NOT NULL DEFAULT 0,
            sound_enabled TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    if ($conn === null) {
        $initialized = true;
    }

    return true;
}

function getAdminNotificationBaseline($conn = null) {
    $pdo = $conn instanceof PDO ? $conn : connectDB();

    $paymentsLastSeenId = 0;
    $reviewsLastSeenId = 0;

    try {
        $paymentsLastSeenId = (int)$pdo->query("SELECT COALESCE(MAX(payment_id), 0) FROM payments")->fetchColumn();
    } catch (Throwable $e) {
        $paymentsLastSeenId = 0;
    }

    try {
        $reviewsLastSeenId = (int)$pdo->query("SELECT COALESCE(MAX(danh_gia_id), 0) FROM danh_gia")->fetchColumn();
    } catch (Throwable $e) {
        $reviewsLastSeenId = 0;
    }

    return [
        'payments_last_seen_id' => max(0, $paymentsLastSeenId),
        'reviews_last_seen_id' => max(0, $reviewsLastSeenId),
        'sound_enabled' => 1,
    ];
}

function getAdminNotificationState($userId, $conn = null) {
    $default = [
        'payments_last_seen_id' => 0,
        'reviews_last_seen_id' => 0,
        'sound_enabled' => 1,
    ];

    $userId = (int)$userId;
    if ($userId <= 0) {
        return $default;
    }

    $pdo = $conn instanceof PDO ? $conn : connectDB();
    ensureAdminNotificationStateTable($pdo);

    $stmt = $pdo->prepare('SELECT payments_last_seen_id, reviews_last_seen_id, sound_enabled FROM admin_notification_state WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        return [
            'payments_last_seen_id' => max(0, (int)($row['payments_last_seen_id'] ?? 0)),
            'reviews_last_seen_id' => max(0, (int)($row['reviews_last_seen_id'] ?? 0)),
            'sound_enabled' => ((int)($row['sound_enabled'] ?? 1) === 1) ? 1 : 0,
        ];
    }

    $state = getAdminNotificationBaseline($pdo);
    $insert = $pdo->prepare('INSERT INTO admin_notification_state (user_id, payments_last_seen_id, reviews_last_seen_id, sound_enabled) VALUES (?, ?, ?, ?)');
    $insert->execute([
        $userId,
        $state['payments_last_seen_id'],
        $state['reviews_last_seen_id'],
        $state['sound_enabled'],
    ]);

    return $state;
}

function saveAdminNotificationState($userId, array $updates, $conn = null) {
    $userId = (int)$userId;
    if ($userId <= 0) {
        return [
            'payments_last_seen_id' => 0,
            'reviews_last_seen_id' => 0,
            'sound_enabled' => 1,
        ];
    }

    $pdo = $conn instanceof PDO ? $conn : connectDB();
    ensureAdminNotificationStateTable($pdo);

    $current = getAdminNotificationState($userId, $pdo);
    $state = [
        'payments_last_seen_id' => max(0, (int)($updates['payments_last_seen_id'] ?? $current['payments_last_seen_id'])),
        'reviews_last_seen_id' => max(0, (int)($updates['reviews_last_seen_id'] ?? $current['reviews_last_seen_id'])),
        'sound_enabled' => array_key_exists('sound_enabled', $updates)
            ? ((((int)$updates['sound_enabled']) === 1) ? 1 : 0)
            : (((int)$current['sound_enabled'] === 1) ? 1 : 0),
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO admin_notification_state (user_id, payments_last_seen_id, reviews_last_seen_id, sound_enabled)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            payments_last_seen_id = VALUES(payments_last_seen_id),
            reviews_last_seen_id = VALUES(reviews_last_seen_id),
            sound_enabled = VALUES(sound_enabled)'
    );
    $stmt->execute([
        $userId,
        $state['payments_last_seen_id'],
        $state['reviews_last_seen_id'],
        $state['sound_enabled'],
    ]);

    return $state;
}

// Upload file
function uploadFile($file, $folderSave) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }
    
    $file_upload = $file;
    $fileExtension = pathinfo($file_upload['name'], PATHINFO_EXTENSION);
    $fileName = rand(10000, 99999) . '_' . time() . '.' . $fileExtension;
    $pathStorage = $folderSave . $fileName;

    $tmp_file = $file_upload['tmp_name'];
    $pathSave = PATH_ROOT . $pathStorage; // Đường dẫn tuyệt đối của file

    // Tạo thư mục nếu chưa tồn tại
    $dir = dirname($pathSave);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (move_uploaded_file($tmp_file, $pathSave)) {
        return $pathStorage;
    }
    return null;
}

// Delete file
function deleteFile($file) {
    if (empty($file)) {
        return false;
    }
    $pathDelete = PATH_ROOT . $file;
    if (file_exists($pathDelete)) {
        return unlink($pathDelete); // Hàm unlink dùng để xóa file
    }
    return false;
}

// Redirect


// Flash message
function setFlashMessage($key, $message) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['flash'][$key] = $message;
}

function getFlashMessage($key) {
    if (!isset($_SESSION)) {
        session_start();
    }
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// Check login
function isLoggedIn() {
    if (!isset($_SESSION)) {
        session_start();
    }
    return isset($_SESSION['user_id']);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?act=auth/login');
        exit();
    }
}

// Require role
function requireRole($role) {
    requireLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: index.php?act=tour/index');
        exit();
    }
}

function sanitizeText($value) {
    if (is_array($value) || is_object($value)) {
        return '';
    }

    $value = trim((string)$value);
    $value = strip_tags($value);
    return preg_replace('/\s+/', ' ', $value) ?? '';
}

function requestString($key, $default = '', $method = 'REQUEST') {
    $source = strtoupper($method) === 'POST' ? $_POST : (strtoupper($method) === 'GET' ? $_GET : $_REQUEST);
    return isset($source[$key]) ? sanitizeText($source[$key]) : $default;
}

function requestInt($key, $default = 0, $method = 'REQUEST') {
    $source = strtoupper($method) === 'POST' ? $_POST : (strtoupper($method) === 'GET' ? $_GET : $_REQUEST);
    if (!isset($source[$key])) {
        return $default;
    }

    $value = filter_var($source[$key], FILTER_VALIDATE_INT);
    return $value !== false ? (int)$value : $default;
}

function requestFloat($key, $default = 0.0, $method = 'REQUEST') {
    $source = strtoupper($method) === 'POST' ? $_POST : (strtoupper($method) === 'GET' ? $_GET : $_REQUEST);
    if (!isset($source[$key])) {
        return $default;
    }

    $raw = str_replace(',', '', (string)$source[$key]);
    return is_numeric($raw) ? (float)$raw : (float)$default;
}

function whitelistRequestParams(array $input, array $allowedKeys) {
    $filtered = [];
    foreach ($allowedKeys as $key) {
        if (array_key_exists($key, $input)) {
            $filtered[$key] = $input[$key];
        }
    }
    return $filtered;
}

function isValidRouteFormat($act) {
    return is_string($act)
        && preg_match('/^[A-Za-z0-9_]+\/[A-Za-z0-9_]+$/', $act) === 1;
}

function validateEmail($email) {
    $email = sanitizeText($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function validatePhone($phone) {
    $phone = preg_replace('/\D+/', '', (string)$phone) ?? '';
    if ($phone === '') {
        return null;
    }

    if (preg_match('/^(0|84)?[3-9][0-9]{8}$/', $phone) !== 1) {
        return null;
    }

    if (strpos($phone, '84') === 0 && strlen($phone) === 11) {
        $phone = '0' . substr($phone, 2);
    }

    return $phone;
}

function validateId($value) {
    if (!is_numeric($value)) {
        return null;
    }
    $id = (int)$value;
    return $id > 0 ? $id : null;
}

function validateMoney($value, $min = 0.0, $max = 999999999999.0) {
    $numeric = is_string($value) ? str_replace(',', '', $value) : $value;
    if (!is_numeric($numeric)) {
        return null;
    }

    $money = (float)$numeric;
    if ($money < $min || $money > $max) {
        return null;
    }

    return $money;
}

function validateDateYmd($value) {
    $value = sanitizeText($value);
    if ($value === '') {
        return null;
    }

    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        return null;
    }

    return $value;
}

function csrfToken($scope = 'default') {
    if (!isset($_SESSION)) {
        session_start();
    }

    $key = 'csrf_' . $scope;
    if (empty($_SESSION[$key])) {
        try {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $_SESSION[$key] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }

    return (string)$_SESSION[$key];
}

function csrfField($scope = 'default') {
    $token = htmlspecialchars(csrfToken($scope), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
}

function verifyCsrfToken($token, $scope = 'default') {
    if (!isset($_SESSION)) {
        session_start();
    }

    $key = 'csrf_' . $scope;
    if (!isset($_SESSION[$key]) || !is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($_SESSION[$key], $token);
}

function setValidationErrors(array $errors, $message = 'Du lieu khong hop le.') {
    if (!isset($_SESSION)) {
        session_start();
    }

    $_SESSION['validation'] = [
        'ok' => false,
        'message' => $message,
        'errors' => $errors
    ];
}

function getValidationErrors() {
    if (!isset($_SESSION)) {
        session_start();
    }

    if (!isset($_SESSION['validation'])) {
        return null;
    }

    $payload = $_SESSION['validation'];
    unset($_SESSION['validation']);
    return $payload;
}

