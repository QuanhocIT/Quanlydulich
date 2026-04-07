<?php

require_once __DIR__ . '/RequestValidator.php';
require_once __DIR__ . '/Authorization.php';
require_once __DIR__ . '/SessionSecurity.php';
require_once __DIR__ . '/PasswordPolicy.php';

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
    return Authorization::isLoggedIn();
}

// Require login
function requireLogin() {
    Authorization::requireLogin();
}

// Require role
function requireRole($role, $redirectAct = null, $message = 'Ban khong co quyen truy cap chuc nang nay.') {
    Authorization::requireRole($role, $redirectAct, $message);
}

function requireAnyRole(array $roles, $redirectAct = null, $message = 'Ban khong co quyen truy cap chuc nang nay.') {
    Authorization::requireRole($roles, $redirectAct, $message);
}

function currentUserRole() {
    return Authorization::currentRole();
}

function hasRole($roles) {
    return Authorization::hasAnyRole($roles);
}

function getRoleHomeRoute($role = null) {
    return Authorization::getRoleHomeRoute($role);
}

function redirectToRoleHome($defaultAct = 'auth/login') {
    Authorization::redirectToRoleHome($defaultAct);
}

function initializeSecureSession($sessionDir = null) {
    SessionSecurity::initialize($sessionDir);
    SessionSecurity::start();
}

function enforceSessionSecurity() {
    return SessionSecurity::enforce();
}

function completeUserLoginSession($userId, $role, $userName, array $extraSessionData = []) {
    SessionSecurity::completeLogin($userId, $role, $userName, $extraSessionData);
}

function logoutCurrentUser($reason = 'logout') {
    SessionSecurity::logout($reason);
}

function logSecurityEvent($event, array $context = []) {
    SessionSecurity::logSecurityEvent($event, $context);
}

function recordFailedLoginAttempt($identifier, $reason = 'invalid_credentials') {
    SessionSecurity::recordFailedLogin($identifier, $reason);
}

function validatePasswordPolicy($password) {
    return PasswordPolicy::validate($password);
}

function isSecurePasswordHash($storedPassword) {
    return PasswordPolicy::isSecureHash($storedPassword);
}

function requiresPasswordChange($storedHash) {
    return PasswordPolicy::needsForceChange($storedHash);
}

function generateTemporaryPassword($length = 14) {
    return PasswordPolicy::generateTemporaryPassword($length);
}

function createOAuthState($provider) {
    return SessionSecurity::generateOAuthState($provider);
}

function verifyOAuthState($provider, $state) {
    return SessionSecurity::verifyOAuthState($provider, $state);
}

function getRouteRoleMatrix() {
    return Authorization::getRouteRoleMatrix();
}

function authorizeRouteAccess($act) {
    return Authorization::enforceRouteAccess($act);
}

function sanitizeText($value) {
    return RequestValidator::sanitizeText($value);
}

function requestString($key, $default = '', $method = 'REQUEST') {
    return RequestValidator::getString($key, $default, $method);
}

function requestInt($key, $default = 0, $method = 'REQUEST') {
    $value = filter_var(RequestValidator::getString($key, (string)$default, $method), FILTER_VALIDATE_INT);
    return $value !== false ? (int)$value : (int)$default;
}

function requestFloat($key, $default = 0.0, $method = 'REQUEST') {
    $raw = str_replace(',', '', RequestValidator::getString($key, (string)$default, $method));
    return is_numeric($raw) ? (float)$raw : (float)$default;
}

function requestRouteAct($default = 'auth/login') {
    $act = requestString('act', $default, 'GET');
    return isValidRouteFormat($act) ? $act : $default;
}

function requestId($key, $default = null, $method = 'REQUEST') {
    return RequestValidator::getId($key, $default, $method);
}

function requestEmail($key, $default = null, $method = 'REQUEST') {
    return RequestValidator::getEmail($key, $default, $method);
}

function requestPhone($key, $default = null, $method = 'REQUEST') {
    return RequestValidator::getPhone($key, $default, $method);
}

function requestMoney($key, $default = null, $method = 'REQUEST', $min = 0.0, $max = 999999999999.0) {
    return RequestValidator::getMoney($key, $default, $method, $min, $max);
}

function validateInputSchema(array $rules, $method = 'POST') {
    $source = strtoupper((string)$method) === 'GET' ? $_GET : (strtoupper((string)$method) === 'POST' ? $_POST : $_REQUEST);
    return RequestValidator::validatePayload($source, $rules);
}

function getRouteInputSchema($act, $method = 'GET') {
    $method = strtoupper((string)$method);
    if ($method !== 'POST') {
        return [];
    }

    $schemas = [
        'auth/register' => [
            'email' => ['type' => 'email', 'required' => true],
            'ho_ten' => ['type' => 'string', 'required' => true, 'max' => 120],
            'password' => ['type' => 'string', 'required' => true, 'min' => 8],
        ],
        'booking/create' => [
            'tour_id' => ['type' => 'id', 'required' => true],
            'ngay_khoi_hanh' => ['type' => 'date', 'required' => true],
            'so_nguoi' => ['type' => 'id', 'required' => true],
        ],
        'booking/hideCompleted' => [
            'booking_id' => ['type' => 'id', 'required' => true],
            'ly_do_an' => ['type' => 'string', 'required' => false, 'max' => 500],
        ],
        'admin/capNhatLuongCoBan' => [
            'nhan_su_id' => ['type' => 'id', 'required' => true],
            'luong_co_ban' => ['type' => 'money', 'required' => true, 'min' => 0],
        ],
        'admin/taoLuongThuong' => [
            'nhan_su_id' => ['type' => 'id', 'required' => true],
            'lich_khoi_hanh_id' => ['type' => 'id', 'required' => true],
            'loai_luong' => ['type' => 'string', 'required' => true],
        ],
        'admin/capNhatYeuCauDacBiet' => [
            'yeu_cau_id' => ['type' => 'id', 'required' => true],
        ],
        'admin/themYeuCauDacBiet' => [
            'booking_id' => ['type' => 'id', 'required' => true],
        ],
        'admin/confirm_payment_received' => [
            'received_amount' => ['type' => 'money', 'required' => true, 'min' => 1],
            'transfer_note' => ['type' => 'string', 'required' => true, 'min' => 3],
        ],
        'admin/deleteNhaCungCap' => [
            'id_nha_cung_cap' => ['type' => 'id', 'required' => true],
            'mat_khau' => ['type' => 'string', 'required' => true, 'min' => 1],
        ],
        'admin/supplierServiceAction' => [
            'dich_vu_id' => ['type' => 'id', 'required' => true],
            'action' => ['type' => 'string', 'required' => true],
        ],
        'hdv/updateCheckInKhach' => [
            'lich_khoi_hanh_id' => ['type' => 'id', 'required' => true],
            'booking_id' => ['type' => 'id', 'required' => true],
            'khach_hang_id' => ['type' => 'id', 'required' => true],
        ],
        'hdv/updateYeuCauDacBiet' => [
            'lich_khoi_hanh_id' => ['type' => 'id', 'required' => true],
            'tour_id' => ['type' => 'id', 'required' => true],
            'khach_hang_id' => ['type' => 'id', 'required' => true],
            'booking_id' => ['type' => 'id', 'required' => true],
            'noi_dung' => ['type' => 'string', 'required' => true, 'min' => 3],
        ],
        'hdv/save_yeu_cau' => [
            'tour_id' => ['type' => 'id', 'required' => true],
            'booking_id' => ['type' => 'id', 'required' => true],
            'tieu_de' => ['type' => 'string', 'required' => true, 'min' => 2],
        ],
        'hdv/save_diem_checkin' => [
            'tour_id' => ['type' => 'id', 'required' => true],
            'ten_diem' => ['type' => 'string', 'required' => true, 'min' => 2],
        ],
        'hdv/save_checkin_khach' => [
            'diem_checkin_id' => ['type' => 'id', 'required' => true],
            'booking_id' => ['type' => 'id', 'required' => true],
            'trang_thai' => ['type' => 'string', 'required' => true],
        ],
        'hdv/delete_yeu_cau' => [
            'id' => ['type' => 'id', 'required' => true],
        ],
        'hdv/save_nhat_ky' => [
            'tour_id' => ['type' => 'id', 'required' => true],
            'tieu_de' => ['type' => 'string', 'required' => true, 'min' => 2],
        ],
        'hdv/delete_nhat_ky' => [
            'id' => ['type' => 'id', 'required' => true],
        ],
        'hdv/save_phan_hoi' => [
            'tour_id' => ['type' => 'id', 'required' => true],
            'loai_danh_gia' => ['type' => 'string', 'required' => true],
            'diem_danh_gia' => ['type' => 'money', 'required' => true, 'min' => 1, 'max' => 5],
            'tieu_de' => ['type' => 'string', 'required' => true, 'min' => 2],
        ],
        'hdv/delete_phan_hoi' => [
            'id' => ['type' => 'id', 'required' => true],
        ],
        'hdv/update_profile' => [
            'email' => ['type' => 'email', 'required' => true],
            'so_dien_thoai' => ['type' => 'phone', 'required' => true],
        ],
    ];

    return $schemas[$act] ?? [];
}

function validateRequestByRoute($act, $method = 'GET') {
    $rules = getRouteInputSchema($act, $method);
    if (empty($rules)) {
        return ['ok' => true, 'data' => [], 'errors' => []];
    }

    return validateInputSchema($rules, $method);
}

function logValidationFailure($context, array $errors, array $meta = []) {
    $logDir = __DIR__ . '/../storage';
    $logFile = $logDir . '/security.log';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    $payload = [
        'time' => date('c'),
        'event' => 'validation_failed',
        'context' => sanitizeText($context),
        'route' => requestString('act', '', 'GET'),
        'ip' => isset($_SERVER['REMOTE_ADDR']) ? sanitizeText((string)$_SERVER['REMOTE_ADDR']) : '',
        'method' => isset($_SERVER['REQUEST_METHOD']) ? sanitizeText((string)$_SERVER['REQUEST_METHOD']) : '',
        'errors' => $errors,
        'meta' => $meta,
    ];

    @file_put_contents($logFile, json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
}

function whitelistRequestParams(array $input, array $allowedKeys) {
    return RequestValidator::whitelistParams($input, $allowedKeys);
}

function isValidRouteFormat($act) {
    return RequestValidator::isValidRouteFormat($act);
}

function validateEmail($email) {
    return RequestValidator::validateEmail($email);
}

function validatePhone($phone) {
    return RequestValidator::validatePhone($phone);
}

function validateId($value) {
    return RequestValidator::validateId($value);
}

function validateMoney($value, $min = 0.0, $max = 999999999999.0) {
    return RequestValidator::validateMoney($value, $min, $max);
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
        SessionSecurity::logSecurityEvent('csrf_validation_failed', [
            'scope' => $scope,
            'reason' => 'missing_token',
        ]);
        return false;
    }

    $valid = hash_equals($_SESSION[$key], $token);
    if (!$valid) {
        SessionSecurity::logSecurityEvent('csrf_validation_failed', [
            'scope' => $scope,
            'reason' => 'token_mismatch',
        ]);
    }

    return $valid;
}

function setValidationErrors(array $errors, $message = 'Du lieu khong hop le.') {
    if (!isset($_SESSION)) {
        session_start();
    }

    logValidationFailure('setValidationErrors', $errors, [
        'message' => sanitizeText((string)$message),
        'user_id' => (int)($_SESSION['user_id'] ?? 0),
    ]);

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

