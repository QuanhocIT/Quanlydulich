<?php

require_once __DIR__ . '/RequestValidator.php';
require_once __DIR__ . '/Authorization.php';
require_once __DIR__ . '/SessionSecurity.php';
require_once __DIR__ . '/PasswordPolicy.php';

// Kết nối CSDL qua PDO
function connectDB() {
    return getPDOConnection();
}

function dbColumnExists(string $tableName, string $columnName, ?PDO $conn = null) {
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

function cacheBaseDir() {
    $dir = __DIR__ . '/../storage/cache/app';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    return $dir;
}

function cacheFilePath(string $key) {
    $normalizedKey = trim((string)$key);
    if ($normalizedKey === '') {
        $normalizedKey = 'default';
    }

    return cacheBaseDir() . '/' . sha1($normalizedKey) . '.json';
}

function cacheRemember(string $key, int $ttlSeconds, callable $resolver, bool $forceRefresh = false) {
    $ttlSeconds = max(1, (int)$ttlSeconds);
    $cachePath = cacheFilePath($key);

    if (!$forceRefresh && is_file($cachePath)) {
        $raw = @file_get_contents($cachePath);
        $payload = $raw ? json_decode($raw, true) : null;
        $expiresAt = (int)($payload['expires_at'] ?? 0);
        if ($expiresAt > time() && array_key_exists('value', (array)$payload)) {
            return $payload['value'];
        }
    }

    $value = $resolver();
    @file_put_contents($cachePath, json_encode([
        'key' => (string)$key,
        'expires_at' => time() + $ttlSeconds,
        'value' => $value,
    ], JSON_UNESCAPED_UNICODE));

    return $value;
}

function cacheForget(string $key) {
    $cachePath = cacheFilePath($key);
    if (is_file($cachePath)) {
        @unlink($cachePath);
    }
}

function cacheForgetByPrefix(string $prefix) {
    $prefix = trim((string)$prefix);
    if ($prefix === '') {
        return;
    }

    $baseDir = cacheBaseDir();
    $files = glob($baseDir . '/*.json');
    if (!is_array($files)) {
        return;
    }

    foreach ($files as $file) {
        if (!is_file($file)) {
            continue;
        }

        $raw = @file_get_contents($file);
        $payload = $raw ? json_decode($raw, true) : null;
        $cacheKey = (string)($payload['key'] ?? '');
        if (strpos($cacheKey, $prefix) === 0) {
            @unlink($file);
        }
    }
}

function ensureAdminNotificationStateTable(?PDO $conn = null) {
    static $initialized = false;

    $pdo = $conn instanceof PDO ? $conn : connectDB();
    if ($initialized && $conn === null) {
        return true;
    }

    try {
        $pdo->query('SELECT user_id, payments_last_seen_id, reviews_last_seen_id, sound_enabled FROM admin_notification_state LIMIT 1');
    } catch (Throwable $e) {
        throw new RuntimeException(
            'Schema admin_notification_state is missing. Please run `php scripts/migrate.php up`. Root cause: ' . $e->getMessage()
        );
    }

    if ($conn === null) {
        $initialized = true;
    }

    return true;
}

function getAdminNotificationBaseline(?PDO $conn = null) {
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

function getAdminNotificationState(int $userId, ?PDO $conn = null) {
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

function saveAdminNotificationState(int $userId, array $updates, ?PDO $conn = null) {
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

function realtimeWebSocketEnabled() {
    return defined('REALTIME_WS_ENABLED') && REALTIME_WS_ENABLED;
}

function realtimeWebSocketPublicUrl() {
    $url = defined('REALTIME_WS_PUBLIC_URL') ? trim((string)REALTIME_WS_PUBLIC_URL) : '';
    return rtrim($url, '/');
}

function realtimeBase64UrlEncode(string $data) {
    return rtrim(strtr(base64_encode((string)$data), '+/', '-_'), '=');
}

function realtimeBase64UrlDecode(string $data) {
    $normalized = strtr((string)$data, '-_', '+/');
    $padding = strlen($normalized) % 4;
    if ($padding > 0) {
        $normalized .= str_repeat('=', 4 - $padding);
    }
    $decoded = base64_decode($normalized, true);
    return ($decoded === false) ? null : $decoded;
}

function buildRealtimeAuthToken(int $userId, string $role, string $scope = 'notifications', ?int $ttlSeconds = null) {
    $userId = (int)$userId;
    $role = trim((string)$role);
    $scope = trim((string)$scope);
    if ($userId <= 0 || $role === '' || $scope === '') {
        return '';
    }

    $secret = defined('REALTIME_HMAC_SECRET') ? (string)REALTIME_HMAC_SECRET : '';
    if ($secret === '') {
        return '';
    }

    $ttl = $ttlSeconds !== null ? (int)$ttlSeconds : (defined('REALTIME_TOKEN_TTL_SECONDS') ? (int)REALTIME_TOKEN_TTL_SECONDS : 120);
    $ttl = max(30, $ttl);
    $now = time();
    $payload = [
        'uid' => $userId,
        'role' => $role,
        'scope' => $scope,
        'iat' => $now,
        'exp' => $now + $ttl,
        'nonce' => bin2hex(random_bytes(8)),
    ];

    $encodedPayload = realtimeBase64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
    $signature = hash_hmac('sha256', $encodedPayload, $secret, true);
    return $encodedPayload . '.' . realtimeBase64UrlEncode($signature);
}

function verifyRealtimeAuthToken(string $token, string $expectedScope = 'notifications') {
    $token = trim((string)$token);
    if ($token === '' || strpos($token, '.') === false) {
        return null;
    }

    [$encodedPayload, $encodedSignature] = explode('.', $token, 2);
    if ($encodedPayload === '' || $encodedSignature === '') {
        return null;
    }

    $secret = defined('REALTIME_HMAC_SECRET') ? (string)REALTIME_HMAC_SECRET : '';
    if ($secret === '') {
        return null;
    }

    $expectedSignature = hash_hmac('sha256', $encodedPayload, $secret, true);
    $providedSignature = realtimeBase64UrlDecode($encodedSignature);
    if ($providedSignature === null || !hash_equals($expectedSignature, $providedSignature)) {
        return null;
    }

    $payloadJson = realtimeBase64UrlDecode($encodedPayload);
    if ($payloadJson === null) {
        return null;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload)) {
        return null;
    }

    $uid = (int)($payload['uid'] ?? 0);
    $role = trim((string)($payload['role'] ?? ''));
    $scope = trim((string)($payload['scope'] ?? ''));
    $exp = (int)($payload['exp'] ?? 0);
    if ($uid <= 0 || $role === '' || $scope === '' || $exp <= time()) {
        return null;
    }
    if ($expectedScope !== '' && $scope !== $expectedScope) {
        return null;
    }

    return [
        'user_id' => $uid,
        'role' => $role,
        'scope' => $scope,
        'iat' => (int)($payload['iat'] ?? 0),
        'exp' => $exp,
    ];
}

function getRealtimeNotificationPayload(string $role, int $userId, ?PDO $conn = null) {
    $role = trim((string)$role);
    $userId = (int)$userId;
    $pdo = $conn instanceof PDO ? $conn : connectDB();

    if ($userId <= 0) {
        return ['success' => false];
    }

    if ($role === 'Admin') {
        require_once __DIR__ . '/../models/ThongBao.php';

        $state = getAdminNotificationState($userId, $pdo);
        $paymentsLastSeenId = (int)($state['payments_last_seen_id'] ?? 0);
        $reviewsLastSeenId = (int)($state['reviews_last_seen_id'] ?? 0);

        $paymentStmt = $pdo->prepare('SELECT COUNT(*) FROM payments WHERE payment_id > ?');
        $paymentStmt->execute([$paymentsLastSeenId]);
        $paymentCount = (int)$paymentStmt->fetchColumn();

        $reviewStmt = $pdo->prepare('SELECT COUNT(*) FROM danh_gia WHERE danh_gia_id > ?');
        $reviewStmt->execute([$reviewsLastSeenId]);
        $reviewCount = (int)$reviewStmt->fetchColumn();

        $thongBaoModel = new ThongBao();
        $requestCount = (int)$thongBaoModel->countYeuCauTourChuaXuLy();

        return [
            'success' => true,
            'payments' => $paymentCount,
            'reviews' => $reviewCount,
            'requests' => $requestCount,
            'dashboard' => $paymentCount + $reviewCount + $requestCount,
            'sound_enabled' => ((int)($state['sound_enabled'] ?? 1) === 1) ? 1 : 0,
        ];
    }

    if ($role === 'KhachHang') {
        require_once __DIR__ . '/../models/ThongBao.php';
        $thongBaoModel = new ThongBao();

        $items = $thongBaoModel->getByNguoiDung($userId, 50);
        $unread = (int)$thongBaoModel->countChuaDoc($userId);

        return [
            'success' => true,
            'unread' => $unread,
            'items' => array_map(static function ($tb) {
                return [
                    'id' => (int)($tb['id'] ?? 0),
                    'tieu_de' => (string)($tb['tieu_de'] ?? ''),
                    'noi_dung' => (string)($tb['noi_dung'] ?? ''),
                    'da_doc' => (int)($tb['da_doc'] ?? 0),
                    'thoi_gian_gui' => (string)($tb['thoi_gian_gui'] ?? ''),
                    'created_at' => (string)($tb['created_at'] ?? ''),
                ];
            }, $items ?: []),
        ];
    }

    if ($role === 'HDV') {
        $nhanSuStmt = $pdo->prepare("SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1");
        $nhanSuStmt->execute([$userId]);
        $nhanSuId = (int)$nhanSuStmt->fetchColumn();
        if ($nhanSuId <= 0) {
            return ['success' => false, 'unread' => 0];
        }

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM thong_bao_hdv WHERE nhan_su_id = ? AND da_xem = 0');
        $countStmt->execute([$nhanSuId]);
        $unread = (int)$countStmt->fetchColumn();

        return [
            'success' => true,
            'unread' => $unread,
        ];
    }

    if ($role === 'NhaCungCap') {
        $supplierStmt = $pdo->prepare('SELECT id_nha_cung_cap FROM nha_cung_cap WHERE nguoi_dung_id = ? LIMIT 1');
        $supplierStmt->execute([$userId]);
        $nhaCungCapId = (int)$supplierStmt->fetchColumn();
        if ($nhaCungCapId <= 0) {
            return ['success' => false, 'pending' => 0];
        }

        $pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM phan_bo_dich_vu WHERE nha_cung_cap_id = ? AND trang_thai = 'ChoXacNhan'");
        $pendingStmt->execute([$nhaCungCapId]);
        $pending = (int)$pendingStmt->fetchColumn();

        return [
            'success' => true,
            'pending' => $pending,
        ];
    }

    return ['success' => false];
}

// Upload file
function uploadFile(array $file, string $folderSave) {
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
function deleteFile(string $file) {
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
function setFlashMessage(string $key, string $message) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['flash'][$key] = $message;
}

function getFlashMessage(string $key) {
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
function requireRole(string|array $role, ?string $redirectAct = null, string $message = 'Ban khong co quyen truy cap chuc nang nay.') {
    Authorization::requireRole($role, $redirectAct, $message);
}

function requireAnyRole(array $roles, ?string $redirectAct = null, string $message = 'Ban khong co quyen truy cap chuc nang nay.') {
    Authorization::requireRole($roles, $redirectAct, $message);
}

function currentUserRole() {
    return Authorization::currentRole();
}

function hasRole(string|array $roles) {
    return Authorization::hasAnyRole($roles);
}

function getRoleHomeRoute(?string $role = null) {
    return Authorization::getRoleHomeRoute($role);
}

function redirectToRoleHome(string $defaultAct = 'auth/login') {
    Authorization::redirectToRoleHome($defaultAct);
}

function initializeSecureSession(?string $sessionDir = null) {
    SessionSecurity::initialize($sessionDir);
    SessionSecurity::start();
}

function enforceSessionSecurity() {
    return SessionSecurity::enforce();
}

function completeUserLoginSession(int $userId, string $role, string $userName, array $extraSessionData = []) {
    SessionSecurity::completeLogin($userId, $role, $userName, $extraSessionData);
}

function logoutCurrentUser(string $reason = 'logout') {
    SessionSecurity::logout($reason);
}

function logSecurityEvent(string $event, array $context = []) {
    SessionSecurity::logSecurityEvent($event, $context);
}

function recordFailedLoginAttempt(string $identifier, string $reason = 'invalid_credentials') {
    SessionSecurity::recordFailedLogin($identifier, $reason);
}

function validatePasswordPolicy(string $password) {
    return PasswordPolicy::validate($password);
}

function isSecurePasswordHash(string $storedPassword) {
    return PasswordPolicy::isSecureHash($storedPassword);
}

function requiresPasswordChange(string $storedHash) {
    return PasswordPolicy::needsForceChange($storedHash);
}

function generateTemporaryPassword(int $length = 14) {
    return PasswordPolicy::generateTemporaryPassword($length);
}

function createOAuthState(string $provider) {
    return SessionSecurity::generateOAuthState($provider);
}

function verifyOAuthState(string $provider, string $state) {
    return SessionSecurity::verifyOAuthState($provider, $state);
}

function getRouteRoleMatrix() {
    return Authorization::getRouteRoleMatrix();
}

function authorizeRouteAccess(string $act) {
    return Authorization::enforceRouteAccess($act);
}

function sanitizeText(mixed $value) {
    return RequestValidator::sanitizeText($value);
}

function requestString(string $key, string $default = '', string $method = 'REQUEST') {
    return RequestValidator::getString($key, $default, $method);
}

function requestInt(string $key, int $default = 0, string $method = 'REQUEST') {
    $value = filter_var(RequestValidator::getString($key, (string)$default, $method), FILTER_VALIDATE_INT);
    return $value !== false ? (int)$value : (int)$default;
}

function requestFloat(string $key, float $default = 0.0, string $method = 'REQUEST') {
    $raw = str_replace(',', '', RequestValidator::getString($key, (string)$default, $method));
    return is_numeric($raw) ? (float)$raw : (float)$default;
}

function requestRouteAct(string $default = 'auth/login') {
    $act = requestString('act', $default, 'GET');
    return isValidRouteFormat($act) ? $act : $default;
}

function requestId(string $key, mixed $default = null, string $method = 'REQUEST') {
    return RequestValidator::getId($key, $default, $method);
}

function requestEmail(string $key, mixed $default = null, string $method = 'REQUEST') {
    return RequestValidator::getEmail($key, $default, $method);
}

function requestPhone(string $key, mixed $default = null, string $method = 'REQUEST') {
    return RequestValidator::getPhone($key, $default, $method);
}

function requestMoney(string $key, mixed $default = null, string $method = 'REQUEST', float $min = 0.0, float $max = 999999999999.0) {
    return RequestValidator::getMoney($key, $default, $method, $min, $max);
}

function validateInputSchema(array $rules, string $method = 'POST') {
    $source = strtoupper((string)$method) === 'GET' ? $_GET : (strtoupper((string)$method) === 'POST' ? $_POST : $_REQUEST);
    return RequestValidator::validatePayload($source, $rules);
}

function getRouteInputSchema(string $act, string $method = 'GET') {
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

function validateRequestByRoute(string $act, string $method = 'GET') {
    $rules = getRouteInputSchema($act, $method);
    if (empty($rules)) {
        return ['ok' => true, 'data' => [], 'errors' => []];
    }

    return validateInputSchema($rules, $method);
}

function logValidationFailure(string $context, array $errors, array $meta = []) {
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

function isValidRouteFormat(mixed $act) {
    return RequestValidator::isValidRouteFormat($act);
}

function validateEmail(mixed $email) {
    return RequestValidator::validateEmail($email);
}

function validatePhone(mixed $phone) {
    return RequestValidator::validatePhone($phone);
}

function validateId(mixed $value) {
    return RequestValidator::validateId($value);
}

function validateMoney(mixed $value, float $min = 0.0, float $max = 999999999999.0) {
    return RequestValidator::validateMoney($value, $min, $max);
}

function validateDateYmd(mixed $value) {
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

function csrfToken(string $scope = 'default') {
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

function csrfField(string $scope = 'default') {
    $token = htmlspecialchars(csrfToken($scope), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
}

function verifyCsrfToken(string $token, string $scope = 'default') {
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

function setValidationErrors(array $errors, string $message = 'Du lieu khong hop le.') {
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

/**
 * Enforce a sliding-window rate limit keyed by an arbitrary string (e.g. "login:{ip}").
 * Persists state as a small JSON file under storage/cache/rate_limit/.
 * Terminates the current request with HTTP 429 if the limit is exceeded.
 *
 * @param string $key         Unique key (e.g. "login:127.0.0.1")
 * @param int    $maxAttempts Maximum number of attempts allowed within the window
 * @param int    $windowSeconds   Time window in seconds
 * @param int    $httpCode    HTTP status code to send when rate-limited (default 429)
 */
function enforceRateLimit(string $key, int $maxAttempts, int $windowSeconds, int $httpCode = 429): void {
    $cacheDir = __DIR__ . '/../storage/cache/rate_limit';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    $file = $cacheDir . '/' . sha1($key) . '.json';
    $now  = time();

    $state = ['hits' => []];
    if (is_file($file)) {
        $raw     = @file_get_contents($file);
        $decoded = $raw !== false ? json_decode((string)$raw, true) : null;
        if (is_array($decoded)) {
            $state = $decoded;
        }
    }

    // Drop hits outside the sliding window
    $hits = is_array($state['hits'] ?? null) ? $state['hits'] : [];
    $hits = array_values(array_filter($hits, fn($t) => is_int($t) && $t > ($now - $windowSeconds)));
    $hits[] = $now;

    @file_put_contents($file, json_encode(['hits' => $hits]), LOCK_EX);

    if (count($hits) > $maxAttempts) {
        logSecurityEvent('rate_limit_exceeded', [
            'key'   => $key,
            'count' => count($hits),
            'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
        http_response_code($httpCode);
        $isJson = strpos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false
               || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau vài phút.'], JSON_UNESCAPED_UNICODE);
        } else {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['error'] = 'Quá nhiều yêu cầu. Vui lòng thử lại sau vài phút.';
            }
            $back = filter_var($_SERVER['HTTP_REFERER'] ?? '', FILTER_VALIDATE_URL) ? $_SERVER['HTTP_REFERER'] : 'index.php?act=auth/login';
            header('Location: ' . $back);
        }
        exit();
    }
}
